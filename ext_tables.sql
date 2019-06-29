#
# Table structure for table 'tx_authorized_preview'
#
CREATE TABLE tx_authorized_preview (
  hash varchar(32) DEFAULT '' NOT NULL,
  tstamp int(11) DEFAULT '0' NOT NULL,
  endtime int(11) DEFAULT '0' NOT NULL,
  config text,
  PRIMARY KEY (hash)
);
