/* Both used for patient and employee */
CREATE TYPE ContactInfo_t AS (
	phone VARCHAR(15),
	email VARCHAR(100))
	INSTANTIABLE 
	MODE DB2SQL; 

CREATE TYPE EmergContact_t AS (
	ename VARCHAR(100),
	ephone VARCHAR(15))
	INSTANTIABLE
	MODE DB2SQL;
	
/* Corresponding Transform methods */
CREATE FUNCTION ContactInfo_transform (contact ContactInfo_t) returns varchar(115) LANGUAGE SQL 
RETURN contact..phone || ',' || contact..email;
	
CREATE TRANSFORM FOR ContactInfo_t db2_program 
(FROM SQL WITH FUNCTION ContactInfo_transform);

CREATE FUNCTION EmergContact_transform (contact EmergContact_t) returns varchar(115) LANGUAGE SQL 
RETURN contact..ename|| ',' || contact..ephone;
	
CREATE TRANSFORM FOR EmergContact_t db2_program 
(FROM SQL WITH FUNCTION EmergContact_transform);

/* First department is the 'hospital' (for executive jobs) */
CREATE TABLE Departments (
	did INT NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1, NO CACHE),
	dname VARCHAR(30) NOT NULL UNIQUE,
	dtype CHAR(1),
	PRIMARY KEY(did),
	CONSTRAINT dtypeValid  /* The loose department types. C->clinical (has physicians), P->pharmacy, E->Exec, F->cafeteria, G->Gift shop */
		CHECK(dtype IN('C','P','E','F','G'))
);

/* Table to store Employee information */
CREATE TABLE Employees (
	eid INT NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1, NO CACHE),
	ename VARCHAR(50),
	sin INT NOT NULL UNIQUE,
	contact ContactInfo_t,		/* Use the UDT */
	dob DATE,
	address VARCHAR(200),
	sdate DATE,
	edate DATE,
	econtact EmergContact_t,	/* Use the UDT */
	jobtype CHAR(1),
	salary INT,
	PRIMARY KEY(eid),
	CONSTRAINT jobtcheck 
		CHECK(jobtype IN('P','F')) /* Emulate ENUM  here. */
);

/* Table of Patients */
CREATE TABLE Patients (
	pid INT NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1, NO CACHE),
	pname VARCHAR(100),
	dob DATE,
	address VARCHAR(300),
	contact ContactInfo_t,
	emergcontact EmergContact_t,
	PRIMARY KEY(pid)
);

/* Table of EDT Record */
CREATE TABLE EDTRecords (
	edtid INT NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1, NO CACHE),
	dateperf DATE NOT NULL,
	activitytype CHAR(1),
	enames VARCHAR(200),	/* Comma-separated names - programming portion */
	description VARCHAR(400),
	duration DECIMAL(8,2) DEFAULT 0,
	outcome VARCHAR(400),
	cost DECIMAL(16,2) DEFAULT 0,
	PRIMARY KEY(edtid),
	CONSTRAINT isValidActivity
		CHECK(activitytype IN('E','D','T')), /* ENUM('E','D','T') */
	CONSTRAINT postitiveCost
		CHECK(cost >= 0)
);

/* Table of Jobs */
CREATE TABLE Jobs (
	jid INT NOT NULL GENERATED ALWAYS AS IDENTITY (START WITH 1, INCREMENT BY 1, NO CACHE),
	jname VARCHAR(50) NOT NULL UNIQUE,
	jtype CHAR(1) NOT NULL,
	PRIMARY KEY(jid),
	CONSTRAINT jobType	/* Ensure jobtype is one of: non-clinical, clinical, executive */
		CHECK(jtype IN('N','C','E'))
);	

/* Billing-specific Table for project part 2 */
CREATE TABLE BillingAccounts (
	pid INT NOT NULL UNIQUE,
	balance DECIMAL(10,2) DEFAULT 0,
	insname VARCHAR(200),
	insacct BIGINT,
	insaddress VARCHAR(300),
	FOREIGN KEY(pid) REFERENCES Patients ON DELETE CASCADE
);

/* Employee-Job relationship */
CREATE TABLE EmployeeJobs (
	eid INT NOT NULL,
	jid INT NOT NULL,
	PRIMARY KEY(eid,jid),
	FOREIGN KEY(eid) REFERENCES Employees ON DELETE CASCADE,
	FOREIGN KEY(jid) REFERENCES Jobs ON DELETE CASCADE
);

/* WorksIn relationship */
CREATE TABLE WorksIn (
	did INT NOT NULL,
	eid INT NOT NULL,
	PRIMARY KEY(eid,did),
	FOREIGN KEY(did) REFERENCES Departments ON DELETE CASCADE,
	FOREIGN KEY(eid) REFERENCES Employees ON DELETE CASCADE
);

/* Separate table for Manages relationship */
CREATE TABLE Manages (
	did INT NOT NULL,
	eid INT NOT NULL,
	PRIMARY KEY(eid,did),
	FOREIGN KEY(did) REFERENCES Departments ON DELETE CASCADE,
	FOREIGN KEY(eid) REFERENCES Employees ON DELETE CASCADE
);

/* Check in and check out a patient */
CREATE TABLE CheckInOuts (
	cid INT NOT NULL GENERATED ALWAYS AS IDENTITY,
	pid INT NOT NULL,
	eidout INT,
	eidin INT NOT NULL,
	indate TIMESTAMP NOT NULL,
	outdate TIMESTAMP,
	totalbill DECIMAL(10,2) DEFAULT 0,
	FOREIGN KEY(pid) REFERENCES Patients ON DELETE CASCADE,
	FOREIGN KEY(eidin) REFERENCES Employees ON DELETE CASCADE,
	FOREIGN KEY(eidout) REFERENCES Employees ON DELETE CASCADE,
	PRIMARY KEY(cid),
	CONSTRAINT validDate
		CHECK(indate < outdate OR outdate = NULL)
);

/* A record of each EDT */
CREATE TABLE PatientExaminations (
	pid INT NOT NULL,
	edtid INT NOT NULL,
	processed INT DEFAULT 0,
	FOREIGN KEY(pid) REFERENCES Patients ON DELETE CASCADE,
	FOREIGN KEY(edtid) REFERENCES EDTRecords ON DELETE CASCADE,
	PRIMARY KEY(pid,edtid)
);