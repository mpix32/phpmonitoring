      Creating the REPLICATION_TIMESTAMP Table and Installing the mysql_tsd Deamon

This must all be done on the replication master server, logged in as root.

Create the REPLICATION_TIMESTAMP table:
    mysql -u root mysql
    create table REPLICATION_TIMESTAMP (LATEST_TIMESTAMP long not null);
    insert into REPLICATION_TIMESTAMP (LATEST_TIMESTAMP) values (unix_timestamp(now()));
    exit

Install and start mysql_tsd (MySQL replication timestamp update daemon).
1) Copy mysql_tsd to the /home directory on the MySQL master server.
2) Copy mysql_tsd.initscript to /etc/init.d/mysql_tsd on the MySQL master server.
3) Log in as root on the MySQL master server and run the following commands:
    dos2unix /home/mysql_tsd /etc/init.d/mysql_tsd
    chown root:root /home/mysql_tsd /etc/init.d/mysql_tsd
    chmod 755 /home/mysql_tsd /etc/init.d/mysql_tsd
    chkconfig mysql_tsd on      (for fedora/centos/redhat)
    /etc/init.d/mysql_tsd start (for fedora/centos/redhat: service mysql_tsd start)

If you haven't started replication already, sync the master's database to the slave's and start replication.

The master's timestamp will be updated at regular intervals by the mysql_tsd daemon.  Each time this happens, the update command will be replicated to the slave, resulting in the slave's timestamp being updated as well.  The MySQLReplicationSSH monitoring plugin compares these timestamps and takes action if it sees the slave falling too far behind the master.
