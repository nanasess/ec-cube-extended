#!/bin/sh

MD5=md5sum
SHA1=sha1sum
TAR=gtar

CURRENT_DIR=`pwd`
TMP_DIR=/tmp
SVN_REPO=http://svn.ec-cube.net/open
ECCUBE_VERSION=${ECCUBE_VERSION:-"2.13.1"}
WRK_DIR=eccube-$ECCUBE_VERSION
SVN_TAGS=$SVN_REPO/tags
#ECCUBE_VERSION=${ECCUBE_VERSION:-"2_12_3"}
#WRK_DIR=version-$ECCUBE_VERSION
#SVN_TAGS=$SVN_REPO/branches

IIS_WRK_DIR="$WRK_DIR"-IIS-p1
IIS_MANIFEST=$IIS_WRK_DIR/manifest.xml
IIS_PARAMS=$IIS_WRK_DIR/parameters.xml
IIS_INSTALL_SQL=$IIS_WRK_DIR/install_mysql.sql
IIS_WEBCONF=$IIS_WRK_DIR/ec-cube/web.config
IIS_INDEX=$IIS_WRK_DIR/ec-cube/index.php
IIS_PATCH=iis.patch

DISTINFO=$CURRENT_DIR/distinfo.txt

if [ ! -d $TMP_DIR ]; then
    mkdir -p $TMP_DIR
fi

cd $TMP_DIR
echo "export $SVN_TAGS/$WRK_DIR repositories..."
svn export $SVN_TAGS/$WRK_DIR $WRK_DIR 1> /dev/null

echo "remove obsolete files..."
rm -rf $WRK_DIR/.setttings
rm -rf $WRK_DIR/.buildpath
rm -rf $WRK_DIR/.project
rm -rf $WRK_DIR/templates
rm -rf $WRK_DIR/convert.php
rm -rf $WRK_DIR/*.sh
rm -rf $WRK_DIR/php.ini
rm -rf $WRK_DIR/patches
rm -rf $WRK_DIR/html/test
rm -rf $WRK_DIR/data/downloads/module/*
find $WRK_DIR -name "dummy" -print0 | xargs -0 rm -rf
find $WRK_DIR -name ".svn" -type d -print0 | xargs -0 rm -rf

echo "set permissions..."
chmod -R a+w $WRK_DIR/html
chmod -R a+w $WRK_DIR/data/cache
chmod -R a+w $WRK_DIR/data/download
chmod -R a+w $WRK_DIR/data/downloads
chmod -R a+w $WRK_DIR/data/Smarty
chmod -R a+w $WRK_DIR/data/class
chmod -R a+w $WRK_DIR/data/logs
chmod -R a+w $WRK_DIR/data/upload
chmod -R a+w $WRK_DIR/data/config

echo "complession files..."

echo "create tar archive..."
$TAR czfp $WRK_DIR.tar.gz $WRK_DIR 1> /dev/null
# gzip -9 $WRK_DIR.tar
mv $WRK_DIR.tar.gz $CURRENT_DIR/

echo "create zip archive..."
zip -r $WRK_DIR.zip $WRK_DIR 1> /dev/null
mv $WRK_DIR.zip $CURRENT_DIR/

echo "create MS WebPI archive..."
mkdir $IIS_WRK_DIR
mv $WRK_DIR $IIS_WRK_DIR/ec-cube
cat << EOF > $IIS_MANIFEST
<msdeploy.iisapp>
  <iisApp path="ec-cube" />
  <dbmysql path="install_mysql.sql"
           commandDelimiter="//"
           removeCommandDelimiter="true"
           waitAttempts="7"
           waitInterval="3000" />
  <setAcl path="ec-cube/html"
          setAclAccess="Modify"
          setAclUser="anonymousAuthenticationUser" />
  <setAcl path="ec-cube/data"
          setAclAccess="Modify"
          setAclUser="anonymousAuthenticationUser" />
</msdeploy.iisapp>
EOF

cat << EOF > $IIS_PARAMS
<parameters>
  <parameter name="Application Path"
             description="アプリケーションをインストールする先の完全なサイト パス（例: Default Web Site/ec-cube）。"
             defaultValue="Default Web Site/ec-cube"
             tags="iisapp">
    <parameterEntry type="ProviderPath"
                    scope="iisapp"
                    match="ec-cube" />
  </parameter>
  <parameter name="SetAclParameter1"
             description="Sets the ACL on the html directory"
             defaultValue="{Application Path}/html"
             tags="Hidden">
    <parameterEntry type="ProviderPath"
                    scope="setAcl"
                    match="ec-cube/html$" />
  </parameter>
  <parameter name="SetAclParameter2"
             description="Sets the ACL on the data directory"
             defaultValue="{Application Path}/data"
             tags="Hidden">
    <parameterEntry type="ProviderPath"
                    scope="setAcl"
                    match="ec-cube/data$" />
  </parameter>
  <parameter name="Database Server"
             defaultValue="localhost"
             tags="MySQL, dbServer">
<parameterEntry type="TextFile"
                    scope="ec-cube\\\\data\\\\config\\\\config.php"
                    match="PlaceHolderForServer" />
<parameterEntry type="TextFile" 
		    scope="install_mysql.sql"
                    match="PlaceholderForDbServer" />
  </parameter>

  <parameter name="Database Name"
             defaultValue="eccube_db"
             tags="MySQL, dbName">
    <parameterEntry type="TextFile"
                    scope="install_mysql.sql"
                    match="PlaceHolderForDb" />
<parameterEntry type="TextFile"
                    scope="ec-cube\\\\data\\\\config\\\\config.php"
                    match="PlaceHolderForDb" />
  </parameter>

  <parameter name="Database Username"
             defaultValue="eccube_user"
             tags="MySQL, DbUsername">
    <parameterEntry type="TextFile"
                    scope="install_mysql.sql"
                    match="PlaceHolderForUser" />
<parameterEntry type="TextFile"
                    scope="ec-cube\\\\data\\\\config\\\\config.php"
                    match="PlaceHolderForUser" />
  </parameter>

  <parameter name="Database Password"
             tags="New, Password, MySQL, DbUserPassword">
    <parameterEntry type="TextFile"
                    scope="install_mysql.sql"
                    match="PlaceHolderForPassword" />
<parameterEntry type="TextFile"
                    scope="ec-cube\\\\data\\\\config\\\\config.php"
                    match="PlaceHolderForPassword" />
  </parameter>

<parameter name="WebMatrix Connection String"
             defaultValue="/* mysql://{Database Username}:{Database Password}@{Database Server}/{Database Name};*/"
             tags="Hidden">
    <parameterEntry kind="TextFile"
                    scope="\\\\data\\\\config\\\\webmatrix.php"
                    match="/\\*\\s*mysql://([^:]*):([^@]*)@([^/]*)/([^;]*);\\*/" />  </parameter>


  <parameter name="Database Administrator"
             defaultValue="root"
             tags="MySQL, DbAdminUsername">
  </parameter>
  <parameter name="Database Administrator Password"
             tags="Password, MySQL, DbAdminPassword">
  </parameter>

  <parameter name="Connection String"
             description="接続文字列"
             defaultValue="Server={Database Server};Database={Database Name};uid={Database Administrator};Pwd={Database Administrator Password};"
             tags="Hidden">
    <parameterEntry type="ProviderPath"
                    scope="dbmysql"
                    match="install_mysql.sql" />
  </parameter>
</parameters>
EOF

cat << EOF > $IIS_INSTALL_SQL
USE PlaceHolderForDb;

DROP PROCEDURE IF EXISTS add_user ;

CREATE PROCEDURE add_user()
BEGIN
DECLARE EXIT HANDLER FOR 1044 BEGIN END;
GRANT ALL PRIVILEGES ON PlaceHolderForDb.* to 'PlaceHolderForUser'@'PlaceholderForDbServer' IDENTIFIED BY 'PlaceHolderForPassword';
FLUSH PRIVILEGES;
END
//

CALL add_user() //

DROP PROCEDURE IF EXISTS add_user //
EOF

cat << EOF > $IIS_WEBCONF
<configuration>
  <system.webServer>
    <security>
      <requestFiltering>
        <denyUrlSequences>
          <add sequence="/data" />
        </denyUrlSequences>
      </requestFiltering>
    </security>
    <defaultDocument>
      <!-- Set the default document -->
      <files>
        <clear />
        <add value="index.php" />
      </files>
    </defaultDocument>
  </system.webServer>
</configuration>
EOF

cat << EOF > $IIS_INDEX
<?php
\$realpath = dirname(__FILE__);
\$scheme = "http";
if (isset(\$_SERVER['HTTPS']) && strtolower(\$_SERVER['HTTPS']) == "on") {
    \$scheme = "https";
}
\$path = str_replace('index.php', '', \$_SERVER["REQUEST_URI"]);
if (\$_SERVER["SERVER_PORT"] == 80 || \$_SERVER["SERVER_PORT"] == 443) {
    \$location = \$scheme . "://" . \$_SERVER["SERVER_NAME"] . \$path . "html/";
} else {
    \$location = \$scheme . "://" . \$_SERVER["SERVER_NAME"] . ":" . \$_SERVER["SERVER_PORT"] . \$path . "html/";
}
\$config_php = \$realpath . '/data/config/config.php';
\$webmatrix_php = \$realpath . '/data/config/webmatrix.php';

if (file_exists(\$config_php)) {
    require_once(\$config_php);

    if (defined('ECCUBE_INSTALL') && ECCUBE_INSTALL == 'ON') {
        \$subject = file_get_contents(\$webmatrix_php);
        preg_match("|/\\*\\s*mysql://([^:]*):([^@]*)@([^/]*)/([^;]*);\\*/|", \$subject, \$matches);
        list(\$all, \$db_user, \$db_password, \$db_server, \$db_name) = \$matches;
        \$admin_force_ssl = ADMIN_FORCE_SSL ? 'TRUE' : 'FALSE';

        \$config_data = "<?php\n"
            . "define ('ECCUBE_INSTALL', 'ON');\n"
            . "define ('HTTP_URL', '" . \$location . "');\n"
            . "define ('HTTPS_URL', '" . \$location . "');\n"
            . "define ('ROOT_URLPATH', '" . \$path . "html/');\n"
            . "define ('DOMAIN_NAME', '');\n"
            . "define ('DB_TYPE', 'mysql');\n"
            . "define ('DB_USER', '" . \$db_user . "');\n"
            . "define ('DB_PASSWORD', '" . \$db_password . "');\n"
            . "define ('DB_SERVER', '" . \$db_server . "');\n"
            . "define ('DB_NAME', '" . \$db_name . "');\n"
            . "define ('DB_PORT', '');\n"
            . "define ('ADMIN_DIR', '" . ADMIN_DIR . "');\n"
            . 'define ("ADMIN_FORCE_SSL", ' . \$admin_force_ssl . ');' . "\n"
            . "define ('ADMIN_ALLOW_HOSTS', '" . ADMIN_ALLOW_HOSTS . "');\n"
            . "define ('AUTH_MAGIC', '" . AUTH_MAGIC . "');\n"
            . "define ('PASSWORD_HASH_ALGOS', '" . PASSWORD_HASH_ALGOS . "');\n"
            . "define ('MAIL_BACKEND', '" . MAIL_BACKEND . "');\n"
            . "define ('SMTP_HOST', '" . SMTP_HOST . "');\n"
            . "define ('SMTP_PORT', '" . SMTP_PORT . "');\n"
            . "define ('SMTP_USER', '" . SMTP_USER . "');\n"
            . "define ('SMTP_PASSWORD', '" . SMTP_PASSWORD . "');\n"
            . "?>\n";
        if(\$fp = fopen(\$config_php,"w")) {
            fwrite(\$fp, \$config_data);
            fclose(\$fp);
        }
    }
}

header("Location: " . \$location . "index.php");
?>
EOF

cd $IIS_WRK_DIR
cat << EOF > $IIS_PATCH
diff -urN eccube-2.11.0-orig/data/config/webmatrix.php eccube-2.11.0-IIS-patched/data/config/webmatrix.php
--- ec-cube/data/config/webmatrix.php	1970-01-01 09:00:00.000000000 +0900
+++ ec-cube/data/config/webmatrix.php	2011-03-24 19:34:45.000000000 +0900
@@ -0,0 +1,4 @@
+<?php
+/* WebMatrix Connection String */
+/* mysql://PlaceHolderForUser:PlaceHolderForPassword@PlaceHolderForServer/PlaceHolderForDb;*/
+?>
diff -urN eccube-2.11.0-orig/html/.user.ini eccube-2.11.0-IIS-patched/html/.user.ini
--- ec-cube/html/.user.ini	1970-01-01 09:00:00.000000000 +0900
+++ ec-cube/html/.user.ini	2011-03-24 19:38:06.000000000 +0900
@@ -0,0 +1,9 @@
+mbstring.language = Japanese
+mbstring.encoding_translation = off
+output_handler = NULL
+magic_quotes_gpc = off
+session.auto_start = 0
+mbstring.internal_encoding = UTF-8
+upload_max_filesize = 5M
+register_globals = off
+date.timezone = Asia/Tokyo
EOF

patch -p0 < $IIS_PATCH
mv $IIS_PATCH ec-cube
find . -name ".htaccess" -delete
zip -r ../$IIS_WRK_DIR.zip . 1> /dev/null
cd ../
mv $IIS_WRK_DIR.zip $CURRENT_DIR/
rm -r $IIS_WRK_DIR

MD5_TGZ=`$MD5 $CURRENT_DIR/$WRK_DIR.tar.gz`
SHA1_TGZ=`$SHA1 $CURRENT_DIR/$WRK_DIR.tar.gz`
MD5_ZIP=`$MD5 $CURRENT_DIR/$WRK_DIR.zip`
SHA1_ZIP=`$SHA1 $CURRENT_DIR/$WRK_DIR.zip`
MD5_IIS=`$MD5 $CURRENT_DIR/$IIS_WRK_DIR.zip`
SHA1_IIS=`$SHA1 $CURRENT_DIR/$IIS_WRK_DIR.zip`

echo "MD5 ($WRK_DIR.tar.gz) = $MD5_TGZ" > $DISTINFO
echo "SHA1 ($WRK_DIR.tar.gz) = $SHA1_TGZ" >> $DISTINFO
echo "MD5 ($WRK_DIR.zip) = $MD5_ZIP" >> $DISTINFO
echo "SHA1 ($WRK_DIR.zip) = $SHA1_ZIP" >> $DISTINFO
echo "MD5 ($IIS_WRK_DIR.zip) = $MD5_IIS" >> $DISTINFO
echo "SHA1 ($IIS_WRK_DIR.zip) = $SHA1_IIS" >> $DISTINFO

echo "finished successful!"
echo $CURRENT_DIR/$WRK_DIR.tar.gz
echo $CURRENT_DIR/$WRK_DIR.zip
echo $CURRENT_DIR/$IIS_WRK_DIR.zip

cat $DISTINFO
