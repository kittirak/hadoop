## 25 ม.ค. 2568

ไฟล์เอกสาร https://www.dropbox.com/scl/fi/1haytvaj193v6soubjggw/HadoopBootcampDS524-2025-01-24.pdf?rlkey=a2wf8mzmuxb27zjs51aom3cmz&dl=0

### **Create EMR Hadoop Cluster** 

1. https://awsacademy.instructure.com/
2. AWS Academy Learner Lab - Associate Services 
   1. Modules
   2. AWS Academy Learner Lab --> Launch AWS Academy Learner Lab
   3. \> Start Lab
   4. พอ AWS ขึ้นสีเขียว กดที่ AWS
3. ไป Amazon EMR(Elastic MapReduce) ** ครั้งแรกให้ค้น EMR ในช่อง Search
4. Create Cluster
   1. Amazon EMR release --> **emr-7.4.0** (เวอร์ชันที่สุดท้ายที่มี sqoop ที่เราจะใช้ทำ workshop)
   2. Application Bundle: **Core Hadoop**
   3. Task (node) --> **Remove instance group**
   4. Provisioning configuration: **เพิ่ม Core nodes เป็น 2 instances**
   5. EC2 key pair: เลือกคีย์ของคุณ
   6. Amazon EMR service role --> Service role: **EMR_DefaultRole**
   7. EC2 instance profile for Amazon EMR --> Instance profile: **EMR_EC2_DefaultRole**
   8. Create Cluster

- ที่เมนู **Applications --> Application UIs on the primary node** จะมีลิงค์เข้าหน้าเว็บของบริการต่าง ๆ ที่ติดตั้งบน Hadoop

- เข้าสู่ระบบ Hadoop ผ่าน ssh ดูที่ **Cluster management --> Connect to the Primary node using SSH**



#### **กำหนดให้ HDFS เก็บ 2 สำเนา (run as root)**

\# Login to root

```
sudo su -
vi /etc/hadoop/conf/hdfs-site.xml
# set dfs.replication=2
```

restart hdfs-namenode service

`systemctl restart hadoop-hdfs-namenode`

รันคำสั่ง exit เพื่อออกจาก root กลับสู่ user hadoop

```
exit
```



## # Exercise with Hadoop

**1. HDFS**

\# 1.1 vi hello.txt #แล้วพิมพ์อะไรยาว ๆ มีคำซ้ำ ๆ กัน ลงไปสักหน่อย จะใช้เป็น Input สำหรับโปรแกรมนับคำในส่วนถัดไป เช่น

```
Hello World
I love you
...
```

\# 1.2 สร้างไดเรกทอรี input บน hdfs

```
hadoop fs -mkdir input
```

\# 1.3 ส่ง hello.txt เข้า HDFS ในไดเรกทอรี Input 

```
hadoop fs -put hello.txt input
```

\# 1.4 เรียกดูไฟล์ในไดเรกทอรี input

```
hadoop fs -ls input/
```

\# 1.5เข้าดูหน้าเว็บของ HDFS ที่พอร์ต 9870 เพื่อดูว่าไฟล์ hello.txt มี 2 สำเนาหรือไม่ http://[primary node]:9870



**2. Using MapReduce (WordCount.java)** 

\# 2.1) สร้างไฟล์ WordCount.java โดยลอก source code WordCount v1.0 จาก cd

https://hadoop.apache.org/docs/current/hadoop-mapreduce-client/hadoop-mapreduce-client-core/MapReduceTutorial.html

**# Build and Pack WordCount**

\# 2.2) create wordcount_classes directory

```
mkdir wordcount_classes
```

\# 2.3) Compile WordCount.java

```
javac -classpath /usr/lib/hadoop/client**/****hadoop-common.jar**:/usr/lib/hadoop/client/hadoop-mapreduce-client-core.jar -d wordcount_classes/ **WordCount.java**
```

\# 2.4) Pack to jar file

```
jar -cvf ./wordcount.jar -C wordcount_classes/ .
```

\# 2.5) Run MapReduce

```
yarn jar ./wordcount.jar WordCount input/* output/wordcount
```

\# 2.6) View Output (output in the last command) 

```
hadoop fs -ls output/wordcount
hadoop fs -cat output/wordcount/part-r-00000
```

\# เข้าดูหน้าเว็บของ Yarn, open web browser to http://[primary node]:8088

\# Python wordcount

https://www.geeksforgeeks.org/hadoop-streaming-using-python-word-count-problem/



**3. Pig script**

\# 3.1 vi wordcount.pig เขียนคำสั่งด้านล่างลงไป

```
A = load 'input/*';
B = foreach A generate flatten(TOKENIZE((chararray)$0)) as word;
C = group B by word;
D = foreach C generate COUNT(B), group;
store D into 'output/wordcount-pig';
```

**# 3.2 run**

```
pig wordcount.pig
```

**# 3.3 view result**

```
hadoop fs -ls output/wordcount-pig
hadoop fs -cat output/wordcount-pig/part-v001-o000-r-00000
```

\----------------------------------------------------------------------------------------------------

## 1 ก.พ. 2568

- สร้าง EMR แบบเดิม แต่ให้เลือกติดตั้ง sqoop ด้วยครับ

**# 4. Hive** 

\# 4.1) Create input data '**member1.txt**' contain following two lines

```
1,Jonh,Smith,2006-02-15 04:34:33
2,Sawasdee,Thailand,2006-01-01 01:01:01
```

\# 4.2) type "hive" in command-line 

```
hive
```

\# 4.3) Create table 

```
CREATE TABLE member (
 mem_id SMALLINT ,
 first_name string,
 last_name string,
 last_update TIMESTAMP
) row format delimited fields terminated by ','
LINES TERMINATED BY '\n' 
STORED AS TEXTFILE location '/user/**hadoop**/member';
```

\# 4.4) Show tables and Describe 

```
show tables;
desc member;
```

\# 4.5) Import csv to table

```
load data local inpath '/home/**hadoop**/member1.txt' into table member;
```

\# 4.6) query

```
select * from member;
```

\# 4.7) count

```
select count(*) from member;
```

\# 4.8) 

```
exit;
```

\# 4.9) create 'member2.csv'

```
3,xxx,yyy,2006-02-15 04:34:33
```

\# 4.10) put member2.csv to member

```
hadoop fs -put member2.csv member
```

\# 4.11) back to hive and query

```
hive
```

**# Hive with JSON**

\# Ref: https://www.mongodb.com/blog/post/using-mongodb-hadoop-spark-part-1-introduction-setup

\# Example Data from mongodb

\#  {"_id":{"$oid":"59d64bda526099306607b280"},"Symbol":"MSFT","Timestamp":"2009-08-24  09:39","Day":24,"Open":24.45,"High":24.49,"Low":24.44,"Close":24.48,"Volume":472225}

\# Example export with --pretty option

```
    "_id": {
        "$oid": "59d64bdc5260993066093020"
    },
    "Symbol": "MSFT",
    "Timestamp": "2010-08-23 16:00",
    "Day": 23,
    "Open": 24.28,
    "High": 24.28,
    "Low": 24.28,
    "Close": 24.28,
    "Volume": 2372833
}
```

\# https://github.com/rcongiu/Hive-JSON-Serde

**# 4.12 ดาวน์โหลด json-serde ไลบารีและวางในตำแหน่งที่ควร (Run as root)** 

```
sudo su -
wget -nv http://www.congiu.net/hive-json-serde/1.3.8/cdh5/json-serde-1.3.8-jar-with-dependencies.jar -O /usr/lib/hive/auxlib/json-serde-1.3.8-jar-with-dependencies.jar
exit
```

\# 4.13 เข้า Hive ด้วย normal user

```
hive
```

\# 4.14 สร้างตาราง minbars

```
CREATE EXTERNAL TABLE minbars (
  Symbol STRING,
  `Timestamp` STRING,
  Day INT,
  Open DOUBLE,
  High DOUBLE,
  Low DOUBLE,
  Close DOUBLE,
  Volume INT
) ROW FORMAT SERDE 'org.openx.data.jsonserde.JsonSerDe'
LOCATION '/user/hadoop/minbars';
exit;
```

**# 4.15 put minbars.json to hdfs**

```
wget -O minbars.json https://www.dropbox.com/scl/fi/h0tpb09u3uuhlqzl9kh1q/minbars.json?rlkey=fkt7pvg97urmbwgtz1izssytn&dl=0
```

\# ดูเนื้อหาในไฟล์

```
cat minbars.json
```

\# วางไฟล์ minbars.json ไปยังไดเรกทอรี /user/nisit/minbars

```
hadoop fs -put minbars.json minbars
```

\# 4.16 ทดลอง query on minbars

```
hive
select * from minbars;
```

\# **4.17  Try Parquet**

```
CREATE EXTERNAL TABLE minbars2 (
Symbol STRING,
`Timestamp` STRING,
Day INT,
Open DOUBLE,
High DOUBLE,
Low DOUBLE,
Close DOUBLE,
Volume INT
) STORED AS **PARQUET**;
```

** คำสั่งข้างต้นไม่ได้ระบุ location ไฟล์จะไปอยู่ในตำแหน่ง default ที่กำหนดใน hive-site.xml สำหรับระบบนี้คือ /user/hive/warehouse

**# เพิ่มข้อมูลเข้าตาราง minbars2 โดยนำมาจากตาราง minbars**

```
insert into minbars2
select * from minbars;
```



**5. Sqoop**

5.1) download sakila-db **(****Run as root****)\**\***

```
sudo su - 
wget https://downloads.mysql.com/docs/sakila-db.tar.gz
```

5.2) untar (แตกไฟล์)

```
tar xvf sakila-db.tar.gz
```

5.3) import to mysql ** เวลา import ถ้ามี error 'cannot create index in FULLTEXT...' ไม่เป็นไรนะครับ 

```
mysql < sakila-db/sakila-schema.sql
mysql < sakila-db/sakila-data.sql
```

5.4) ดูข้อมูลในฐานข้อมูล 

```
mysql -u root
use sakila;
show tables;
select * from actor;
```

5.5) grant สิทธิ์การใช้งานฐานข้อมูล sakila ให้ผู้ใช้ชื่อ dbuser และมีรหัสผ่านว่า password ถ้าไม่มีผู้ใช้นี้อยู่ระบบจะสร้างให้

```
grant all privileges on sakila.* to 'dbuser'@'%' identified by 'password' with grant option;
exit
```

5.6) Install MySQL JDBC (Java DataBase Connectivity) (**run as root**)

```
wget https://dev.mysql.com/get/Downloads/Connector-J/mysql-connector-java-5.1.49.tar.gz
tar xvf mysql-connector-java-5.1.49.tar.gz 
mkdir -p /usr/share/java
cp mysql-connector-java-5.1.49/mysql-connector-java-5.1.49.jar /usr/share/java
ln -sf /usr/share/java/mysql-connector-java-5.1.49.jar /usr/share/java/mysql-connector-java.jar 
ln -sf /usr/share/java/mysql-connector-java.jar /usr/lib/sqoop/lib/mysql-connector-java.jar 
```

5.7) **(run as user)** นำข้อมูลจากตาราง actor ในฐานข้อมูล MySQL เข้าสู่ไดเรกทอรี /user/hadoop/member (ตาราง member ใน hive)

```
sqoop import --connect jdbc:mysql://ip-172-31-78-222/sakila --username dbuser -P --table actor --append --target-dir /user/hadoop/member
```

5.8) นำข้อมูลจากตาราง film_actor ใน MySQL เข้าสู่ hive และสร้างเป็นตารางในชื่อเดียวกัน

```
sqoop import --connect jdbc:mysql://ip-172-31-78-222/sakila --username dbuser -P \
--table film_actor \
--hive-import \
--create-hive-table \
--hive-table film_actor
```

ดูคำสั่ง sqoop เพิ่มเติมที่ https://sqoop.apache.org/docs/1.4.6/SqoopUserGuide.html

5.9) เก็บรหัสผ่านฐานข้อมูลในไฟล์ เพื่อไม่ต้องการกรอกทุกครั้งที่ใช้ sqoop จะสะดวกเวลาตั้งเวลารันสคริปต์

```
echo -n "password" > .password
hadoop fs -put .password
hadoop fs -chmod 600 .password
```

5.10) ทดลองรัน แบบให้อ่านรหัสผ่านจากไฟล์

```
sqoop import --connect jdbc:mysql://[IP of MySQL host]/sakila --username dbuser --password-file .password --table actor --append --target-dir /user/hadoop/member
```



### 8 ก.พ. 2568

Prerequisite

- สร้าง EMR แบบเดิม ให้เลือกติดตั้ง sqoop ด้วยครับ

- สร้างตาราง member ใน hive ตามข้อ 4.3

- หัวข้อ sqoop ข้อ 5.1-5.6

**6. Hue**

เข้าดูหน้าเว็บของ Hue, open web browser to http://[primary node]:**8888**

*** เข้ามาคร้งแรก เป็นการกำหนด username & password  และบัญชีผู้ใช้นี้จะเป็น admin ของ Hue  โปรดพิมพ์รหัสผ่านด้วยความตั้งใจเพราะเขาให้กรอกครั้งเดียว username แนะนำให้ใช้ hadoop จะได้เหมือน command-line ***

6.1) ทดลองรัน hive ที่เมนู editor --> hive และ browser file ที่เมนู File

6.2) กำหนดค่า timezone ในคอนฟิกของ Hue ให้ถูกต้อง

```
vi /etc/hue/conf/hue.ini
# set time zone to Asia/Bangkok
time_zone = Asia/Bangkok

# restart hue service
systemctl restart hue
```

6.3) แบบฝึกหัดเรื่อง Marker map (ดูภาพประกอบในไฟล์นำเสนอ)

6.3.1) ดาวน์โหลดไฟล์ excel ข้อมูลพิกัดที่ตั้งตำบลจากลิงค์นี้ https://data.go.th/dataset/item_c6d42e1b-3219-47e1-b6b7-dfe914f27910

6.3.2) แปลงไฟล์ excel เป็น csv

6.3.3) เลือกที่เมนู Tables แล้วที่หน้าจอฝั่งขวา เลือก Hive เลือกฐานข้อมูล Default

6.3.4) เราจะสร้างตารางใหม่ใน Hive โดยใช้ไฟล์ TAMBON.csv นี้เป็นหา ให้กด +New  แล้วอัพโหลดไฟล์ TAMBON.CSV พิจารณาแล้วกดดำเนินการต่อจนระบบสร้างตาราง  tambon ให้



**7. OOZIE**

https://www.tutorialspoint.com/apache_oozie/apache_oozie_workflow.htm

7.1) สร้างตาราง day_count บน hive

```
create table day_count(
 count_date timestamp,
 mem_count int
);
```

7.2) สร้าง sqoop script ต่อไปนี้ ตั้งชื่อว่า **import from sakila.actor**

```
import --connect jdbc:mysql://ip-172-31-91-120/sakila --username dbuser --password-file .password --table actor --append --target-dir /user/hadoop/member
```

หมายเหตุ sqoop บนหน้า hue จะเรียกใช้ JDBC จาก oozie sharelib  ฉะนั้นต้องทำขั้นตอนต่อไปนี้ ที่เครื่องที่ Oozie server ทำงานอยู่ (run as root) 

```
sudo -u oozie hadoop fs -put /usr/share/java/mysql-connector-java.jar /user/oozie/share/lib/sqoop/
```

7.3) สร้าง hive script ต่อไปนี้ ตั้งชื่อว่า **day_count**

```
insert into day_count
select current_timestamp(), count(*) from member;
```

7.4) สร้าง oozie workflow เอาข้อ 2 กับ 3 มาวางเรียงกันตามลำดับ แล้วทดสอบรัน

7.5) ตั้งเวลารัน oozie workflow ด้วย schedule