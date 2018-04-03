-- MySQL Schema File

CREATE TABLE comanage_people (
  id                 INTEGER NOT NULL AUTO_INCREMENT,
  kerberosid         VARCHAR(512),
  lastname           VARCHAR(512),
  firstname          VARCHAR(512),
  middlename         VARCHAR(512),
  institution        VARCHAR(512),
  department         VARCHAR(512),
  division           VARCHAR(512),
  academicdepartment VARCHAR(512),
  academictitle      VARCHAR(512),
  title              VARCHAR(512),
  email              VARCHAR(512),
  managernumber      VARCHAR(512),
  positionnumber     VARCHAR(512),
  isfulltime         BOOLEAN,
  isparttime         BOOLEAN,
  isperdiem          BOOLEAN,
  ishhmi             BOOLEAN,
  isenabled          BOOLEAN,
  isemployee         BOOLEAN,
  isfaculty          BOOLEAN,
  mustdisclose       BOOLEAN,
  createddate        DATETIME,
  lastmodifieddate   DATETIME,
  supervisornumber   VARCHAR(512),
  employeeid         VARCHAR(512),
  PRIMARY KEY(id)
);

CREATE INDEX comanage_people_i1 ON comanage_people (kerberosid);
