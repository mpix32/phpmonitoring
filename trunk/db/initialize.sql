drop database if exists phpmonitor;
create database phpmonitor;

use phpmonitor;

create table monitors (
	id int not null primary key auto_increment,
	name varchar(100) not null,
	frequency int not null,
	lastRun DATETIME,
	lastError DATETIME,
	notifyAdmin int not null,
	currentStatus int not null,
	pluginType varchar(100) not null,
	pluginInput text,
	active int
	)
engine=MyISAM;

create table logging (
	id int not null primary key auto_increment,
	monitorId int not null,
	dateTime DATETIME not null,
	responseTimeMs int not null,
	measuredValue varchar(100) not null,
	returnContent text,
	status TINYINT not null
	)
engine=MyISAM;

ALTER TABLE logging ADD INDEX (dateTime);  
ALTER TABLE logging ADD INDEX (monitorId);  

create table settings (
	cronIterations int not null,
	settings text
	)
engine=MyISAM;
insert into settings (cronIterations,settings) values(1,'');
