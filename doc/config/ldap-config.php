<?
/*****************************************************************************************
  Fileame: ldap-config.php

  Purpose: Contains all settings for ldap connectiving and attribute mapping

  Created: 11-20-2005

******************************************************************************************/

/************************************************************
	LDAP Connectivity
************************************************************/

//your ldap server uri
define("LDAP_SERVER","ldap://ldap.domain.com");

//your ldap server port
define("LDAP_PORT","389");

//the dn to bind to your server with
define("BIND_DN","cn=root,dc=domain,dc=com");

//the password of the above specified dn
define("BIND_PASSWORD","secret");

//your search attribute base for accounts
define("LDAP_BASE","ou=people,dc=domain,dc=com");

//a search filter to limit valid accounts to
define("LDAP_FILTER","(uid=*)");

//password encrytion in database
//define("LDAP_CRYPT","MD5");

//ldap protocol
define("LDAP_PROTOCOL","3");

//default group id for a new account
define("DEFAULT_GID","100");

//base of our tree
define("LDAP_ROOT","dc=domain,dc=com");

//if an account doesn't have a permissions entry, automatically create one
//with the following permissions set.  This is from a set bit using the permissions
//in permissions.xml. 
//EDIT_PASSWORD,EDIT_PROFILE,INSERT_OBJECTS,MANAGE_GROUP,MANAGE_USERS,ADMIN
//111000 -> 56 -> EDIT_PASSWORD,EDIT_PROFILE, & INSERT_OBJECTS
define("LDAP_PERMCREATE","56");

/***********************************************************
	Attribute Mapping
***********************************************************/
define("LDAP_UID","uid");
define("LDAP_UIDNUMBER","uidNumber");
define("LDAP_GIDNUMBER","gidNumber");
define("LDAP_USERPASSWORD","userPassword");
define("LDAP_CN","cn");
define("LDAP_SN","sn");
define("LDAP_GECOS","gecos");
define("LDAP_TELEPHONENUMBER","telephoneNumber");
define("LDAP_GIVENNAME","givenName");
define("LDAP_MAIL","mail");

//your dn in your directory should look like this:
//<UID>=<login>,<LDAP_BASE>
//ex: uid=mylogin,ou=people,dc=mydomain,dc=com
