# วิธีติดตั้ง Cloudera Hadoop ด้วย Cloudera Manager
โดย กิตติรักษ์ ม่วงมิ่งสุข

#### คุยกันก่อน

- ขั้นตอนตามเอกสารนี้ อ้างอิงกับระบบของ http://OpenLandScape.cloud (OLS) 
- เราสร้าง Hadoop Cluster ขนาด 6 โหนด (node) 1 edge, 2 master, 3 worker
- ควรเตรียมช่วงวันเวลาที่สะดวกในการติดตั้งต่อเนื่องกัน เพราะระบบคิดค่าใช้จ่ายตามเวลาที่เราใช้งาน
- มีวีดีโอสอนอยู่ 4 คลิป ค่อยทำตามไปได้ครับ

**ต่อไปนี้เป็นคลิปเริ่มต้น Part 1 Introduction 11 minutes‬**

[![Cloudera Hadoop Installation - Part 1 Introduction 11 minutes‬](https://img.youtube.com/vi/j4tafWYtLKM/0.jpg)](https://www.youtube.com/watch?v=j4tafWYtLKM)

#### สร้างและเตรียม Instance

1. สร้าง instance บน OLS โดยใช้เป็นระบบปฏิบัติการ CentOS7 จำนวน 6 เครื่อง หรือโหนด (node) ประกอบด้วย
    1.1 เครื่อง Edge Node จำนวน 1 เครื่อง ใช้แรม 16GB Type F
    1.2 เครื่อง Master Node จำนวน 2 เครื่อง ใช้แรม 4GB Type D
    1.3 เครื่อง Worker Node จำนวน 3 เครื่อง ใช้แรม 4GB Type D
    1.4 ทุกเครื่องให้ใช้ security group ที่เปิดพอร์ตต่อไปนี้ 22, 7180, 8888, 8889, 9870, 10000

| 22        | ssh              |
| --------- | ---------------- |
| 7180      | cloudera manager |
| 8888,8889 | Hue              |
| 9870      | HDFS web         |
| 10000     | hive beeline     |

  1.5 วิธีการสร้าง Instance
  1) เลือก OS --> CentOS7
  2) เลือก Package --> D (เฉพาะเครื่อง edge ใช้ F)
  3) เลือกการ Authentication แนะนำว่าตามหลักความปลอดภัยควรใช้ key pair สร้างคีย์เดียวแล้วใช้กับทุก instance ครั้งแรกต้องสร้างคีย์ Add new key --> Add key Pair --> ตั้งชื่อคีย์ แล้วดาวน์โหลเก็บไว้ **รักษาให้ดีถ้าหายจะเข้าเครื่องไม่ได้** ครั้งต่อไปเลือกคีย์ที่สร้างไว้แล้ว
  4) ตั้งชื่อ Hostname เรียงกันไป worker3, worker2, worker1, master2, master1, edge

2. วิธีการใช้งาน putty เพื่อ ssh ไปยัง instance บนคลาวด์
    2.1 ดาวน์โหลด putty และ puttygen จากลิงค์ต่อไปนี้
    https://www.chiark.greenend.org.uk/~sgtatham/putty/latest.html

​       2.2 ใช้ puttygen แปลง (convert) private key เนื่องคีย์ที่ได้มา (นามสกุล .pem) เป็นแบบที่ใช้กับ ssh บน Unix/Linux ใช้กับ putty บนวินโดวส์ไม่ได้ จึงต้องแปลงให้เป็นรูปแบบที่ใช้งานได้ ให้ดูในเอกสาร https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/putty.html

​       2.3 ใช้ putty ติดต่อเข้าไปยังเครื่อง edge วิธีการใช้งานก็มีตามลิงค์เอกสารในข้อ 2.2

​       2.4 นำ private key (ไฟล์ .pem) ไปใส่ยังเครื่อง edge เพื่อใช้ติดต่อไปยังเครื่องอื่น ๆ โดยคัดลอกไปวางไปยังตำแหน่ง default เวลา ssh จะได้ไม่ต้องระบุว่าใช้ key ไหน
vi /root/.ssh/id_rsa

คัดลอก เนื้อหาในไฟล์ .pem มาวาง บันทึกแล้วจากนั้นสั่งคำสั่งต่อไปนี้ปรับ permission ให้เฉพาะผู้ใช้นั้นเท่านั้นสามารถยุ่งกับไฟล์นี้ได้

`chmod 600 .ssh/id_rsa`

**Cloudera Hadoop Installation - Part 2 OS Preparation ‪27 minutes**

[![Cloudera Hadoop Installation - Part 2 OS Preparation ‪27 minutes‬](https://img.youtube.com/vi/QKw7J_Muuh0/0.jpg)](https://www.youtube.com/watch?v=QKw7J_Muuh0)


3. กำหนดไอพีและชื่อเครื่อง ในไฟล์ /etc/hosts เปลี่ยน xx เป็นไอพีที่คลาวด์จัดสรรมาให้ ดูที่หน้า instance (ใช้ไอพีวง public 203) ชื่อเครื่องแบบ Fully Qualified Domain Name (FQDN) เป็นเรื่องสำคัญ อ้างอิง https://docs.cloudera.com/documentation/enterprise/6/6.3/topics/configure_network_names.html#configure_network_names

203.15x.xxx.xxx edge.example.com edge
203.15x.xxx.xxx master1.example.com master1
203.15x.xxx.xxx master2.example.com master2
203.15x.xxx.xxx worker1.example.com worker1
203.15x.xxx.xxx worker2.example.com worker2
203.15x.xxx.xxx worker3.example.com worker3

4. ตั้งชื่อเครื่องทุกเครื่อง (** สั่งทีละบรรทัด เพราะเป็นการติดต่อระหว่างเครื่องครั้งแรกจะมีการถาม Y/N เพื่อรับคีย์)

`hostnamectl set-hostname edge.example.com`
`ssh master1 hostnamectl set-hostname master1.example.com`
`ssh master2 hostnamectl set-hostname master2.example.com`
`ssh worker1 hostnamectl set-hostname worker1.example.com`
`ssh worker2 hostnamectl set-hostname worker2.example.com`
`ssh worker3 hostnamectl set-hostname worker3.example.com`

#### 5. เตรียม parallel command
5.1 สร้างไฟล์ชื่อ hosts ในโฮมไดเรกทอรี ใส่ชื่อเครื่องทุกเครื่องลงไป เว้น edge node เพื่อเอาไว้ให้สคริปต์มาอ่านแล้วส่งคำสั่งไปยังเครื่องที่ระบุไว้
vi hosts

คนที่ใช้ vi ไม่เป็นให้ใช้ gedit แล้วใส่ชื่อเครื่องดังต่อไปนี้ลงไป
master1
master2
worker1
worker2
worker3

5.2 ติดตั้งโปรแกรม pdsh เพื่อให้สามารถสั่งคำสั่งไปยังหลายเครื่องในคำสั่งเดียวได้
`yum install -y epel-release`
`yum install -y pdsh`

ใช้งาน pdsh ส่งคำสั่ง hostname ไปยังทุกเครื่อง
`pdsh -w ^hosts hostname`

5.3 สร้างไดเรกทอรี /root/bin แล้วสร้าง script สำหรับคัดลอกไฟล์ข้ามไปยังทุกเครื่องในคลัสเตอร์
`mkdir bin`

**vi /root/bin/pscp**

#!/bin/sh
for i in cat /root/hosts
do
  scp $1 ${i}:$1
done

**Change permission setting to excecute**
`chmod +x /root/bin/pscp`

5.4 send /etc/hosts to all host
`pscp /etc/hosts`

5.5 Disable SELinux
`sed -i 's/SELINUX=enforcing/SELINUX=permissive/g' /etc/selinux/config`
`getenforce`
`setenforce 0`
`pdsh -w ^hosts setenforce 0`
`pscp /etc/selinux/config`

#### 6. setup DBMS (in this workshop use mariaDB)

6.1 install mariadb
`yum install -y mariadb-server`

6.2 start mariadb
คำสั่ง enable ใช้สำหรับทำ autostart ให้บริการนั้นเริ่มทำงานทุกครั้งที่เปิดเครื่อง
`systemctl enable mariadb`

คำสั่ง start ใช้เริ่มการทำงาน ยังมี stop และ status อีกที่ใช้ได้
`systemctl start mariadb`

6.3 setup MySQL JDBC
`yum install -y wget bash-completion-extras`
`wget https://dev.mysql.com/get/Downloads/Connector-J/mysql-connector-java-5.1.46.tar.gz`
`tar xvf mysql-connector-java-5.1.46.tar.gz`
`mkdir -p /usr/share/java`
`cp mysql-connector-java-5.1.46/mysql-connector-java-5.1.46.jar /usr/share/java`
`ln -sf /usr/share/java/mysql-connector-java-5.1.46.jar /usr/share/java/mysql-connector-java.jar`


# Cloudera Enterprise 6 Installation
#### Cloudera Manager 6.3 Installation Guide
#### วิธีการติดตั้งนี้อ้างอิงจาก https://www.cloudera.com/documentation/enterprise/6/6.3/topics/installation.html

**Part 3 Cloudera Manager 6 Installation ‪40 minutes‬**

[![Cloudera Hadoop Installation - Part 3 Cloudera Manager 6 Installation ‪40 minutes‬](https://img.youtube.com/vi/oCuWBitA_ys/0.jpg)](https://www.youtube.com/watch?v=oCuWBitA_ys)


#### 0. install repository
`wget http://210.4.137.246/repos/cm6/6.3.1/cloudera-manager.repo -P /etc/yum.repos.d/`

แก้ไขสองบรรทัดต่อไปนี้ในไฟล์ /etc/yum.repos.d/cloudera-manager.repo เพื่อเปลี่ยนมาใช้ local repository ที่เตรียมไว้ให้

baseurl=http://210.4.137.246/repos/cm6/6.3.1/
gpgkey=http://210.4.137.246/repos/cm6/6.3.1/RPM-GPG-KEY-cloudera


#### 1 Install cloudera manager
`yum install -y oracle-j2sdk1.8`
`yum install -y cloudera-manager-server`

#### 2.Creating Databases for Cloudera Software
ref. https://docs.cloudera.com/documentation/enterprise/6/6.3/topics/install_cm_mariadb.html#install_cm_mariadb_newdbs

`mysql`
`CREATE DATABASE scm DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;`
`GRANT ALL ON scm.* TO 'scm'@'%' IDENTIFIED BY 'password';`
`grant all on scm.* to 'scm'@'localhost' identified by 'password';`
`grant all on scm.* to 'scm'@'edge.example.com' identified by 'password';`

`CREATE DATABASE amon DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;`
`GRANT ALL ON amon.* TO 'amon'@'%' IDENTIFIED BY 'password';`
`grant all on amon.* to 'amon'@'localhost' identified by 'password';`
`grant all on amon.* to 'amon'@'edge.example.com' identified by 'password';`

`CREATE DATABASE rman DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;`
`GRANT ALL ON rman.* TO 'rman'@'%' IDENTIFIED BY 'password';`
`grant all on rman.* to 'rman'@'localhost' identified by 'password';`
`grant all on rman.* to 'rman'@'edge.example.com' identified by 'password';`

`CREATE DATABASE hue DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;`
`GRANT ALL ON hue.* TO 'hue'@'%' IDENTIFIED BY 'password';`
`grant all on hue.* to 'hue'@'localhost' identified by 'password';`
`grant all on hue.* to 'hue'@'edge.example.com' identified by 'password';`

`CREATE DATABASE metastore DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;`
`GRANT ALL ON metastore.* TO 'hive'@'%' IDENTIFIED BY 'password';`
`grant all on metastore.* to 'hive'@'localhost' identified by 'password';`
`grant all on metastore.* to 'hive'@'edge.example.com' identified by 'password';`

`CREATE DATABASE sentry DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;`
`GRANT ALL ON sentry.* TO 'sentry'@'%' IDENTIFIED BY 'password';`
`grant all on sentry.* to 'sentry'@'localhost' identified by 'password';`
`grant all on sentry.* to 'sentry'@'edge.example.com' identified by 'password';`

`CREATE DATABASE nav DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;`
`GRANT ALL ON nav.* TO 'nav'@'%' IDENTIFIED BY 'password';`
`grant all on nav.* to 'nav'@'localhost' identified by 'password';`
`grant all on nav.* to 'nav'@'edge.example.com' identified by 'password';`

`CREATE DATABASE navms DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;`
`GRANT ALL ON navms.* TO 'navms'@'%' IDENTIFIED BY 'password';`
`grant all on navms.* to 'navms'@'localhost' identified by 'password';`
`grant all on navms.* to 'navms'@'edge.example.com' identified by 'password';`

`CREATE DATABASE oozie DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;`
`GRANT ALL ON oozie.* TO 'oozie'@'%' IDENTIFIED BY 'password';`
`grant all on oozie.* to 'oozie'@'localhost' identified by 'password';`
`grant all on oozie.* to 'oozie'@'edge.example.com' identified by 'password';`

`exit;`


#### 3. Set up the Cloudera Manager Database
`/opt/cloudera/cm/schema/scm_prepare_database.sh mysql scm scm password`
`cat /etc/cloudera-scm-server/db.properties`

#### 4. Start the Cloudera Manager Server
`systemctl enable cloudera-scm-server`
`systemctl start cloudera-scm-server`

go to http://<server_host>:7180

Username: admin
Password: admin

#### 5. ถ้ารอสัก 2 นาทีแล้วหน้าเว็บไม่ขึ้น ให้ดู log ว่ามี error หรือไม่ ถ้ามีให้เอา error ไปค้นเพื่อแก้ปัญหา แก้ไม่ได้เอา error มาให้ผมดูครับ
`tail -n 500 /var/log/cloudera-scm-server/cloudera-scm-server.log`


#### 6. ถ้าหน้าเว็บ Cloudera Manager ขึ้นให้ทำตามเอกสารแนบ ตั้งแต่หน้าที่ 9

**ส่วนต่อไปนี้เป็นข้อมูลที่ต้องกรอกบนหน้าเว็บระหว่างติดตั้ง และคำสั่งที่รันเพื่อปรับแต่งค่า เตรียมไว้ให้เพื่ออำนวยความสะดวกในการติดตั้ง เมื่อคลิกติดตั้งตามขั้นตอนต้องกรอกข้อมูลเมื่อไหร่ ก็ให้มาดูที่ส่วนต่อไปนี้**

edge.example.com
master1.example.com
master2.example.com
worker1.example.com
worker2.example.com
worker3.example.com

**Select Repository**
http://210.4.137.246/repos/cm6/6.3.1/

**แก้ไข remote parcel repo บรรทัดแรก เปลี่ยนเป็น**
http://210.4.137.246/repos/cdh6/6.3.2/parcels/

#### 7. Tuning kernel parameter for Hadoop

#### 7.1 Cloudera recommends setting /proc/sys/vm/swappiness to a maximum of 10.

`sysctl -w vm.swappiness=10`
`sysctl -p`

`pdsh -w ^hosts sysctl -w vm.swappiness=10`
`pdsh -w ^hosts sysctl -p`

`echo "vm.swappiness=10" > /etc/sysctl.d/hadoop.conf`
`pscp /etc/sysctl.d/hadoop.conf`

#### 7.2 Set Transparent Huge Page Compaction is enabled and can cause significant performance problems. Run "echo never > /sys/kernel/mm/transparent_hugepage/defrag" and "echo never > /sys/kernel/mm/transparent_hugepage/enabled" to disable this

`echo never > /sys/kernel/mm/transparent_hugepage/defrag`
`echo never > /sys/kernel/mm/transparent_hugepage/enabled`
`pdsh -w ^hosts 'echo never > /sys/kernel/mm/transparent_hugepage/defrag'`
`pdsh -w ^hosts 'echo never > /sys/kernel/mm/transparent_hugepage/enabled'`

`chmod +x /etc/rc.d/rc.local`
`echo 'echo never > /sys/kernel/mm/transparent_hugepage/defrag' >> /etc/rc.d/rc.local`
`echo 'echo never > /sys/kernel/mm/transparent_hugepage/enabled' >> /etc/rc.d/rc.local`

`pscp /etc/rc.d/rc.local`
`pdsh -w ^hosts chmod +x /etc/rc.d/rc.local`


#### database name and user (all account password are "password")
#DBname #username #password
Hive
metastore hive password

Acrivity Monitor
amon amon password

Reports Manager
rman rman password

Oozie Server
oozie oozie password

Hue
hue hue password

**Part 4 HDFS & YARN High Availability ‪12 minutes**

[![Cloudera Hadoop Installation - Part 4 HDFS & YARN High Availability ‪12 minutes‬](https://img.youtube.com/vi/JXOyyT2M5i8/0.jpg)](https://www.youtube.com/watch?v=JXOyyT2M5i8)


#### 8. การเซ็ตระบบ High Availability ให้ HDFS
ที่หน้าเว็บ Cloudera Manager เลือกไปที่ HDFS -> Actions --> Enable High Availability แล้วทำตามขั้นตอนที่ระบบแจ้ง

JournalNode Hosts is edge, master1, master2
JournalNode Edits Directory fill "/dfs/jn"

#### 9. High Availability Yarn
ที่หน้าเว็บ Cloudera Manager เลือกไปที่ Yarn -> Actions --> Enable High Availability
