# Presto Installation Guide

**Presto 0.233.1 require jdk1.5.1 up**

#### Scenario

- we use tiny size VM (coordinator 5GB  of RAM, worker 2GB) for testing. 
- my master host is 'namenode1' , don't remember to replace to your **coordinator** hostname.

#### Presto architecture
![alt text](https://prestodb.io/static/presto-overview.png "presto architecture")


#### Install JDK

```
tar xvzf jdk-8u202-linux-x64.tar.gz -C /opt

alternatives --install "/usr/bin/java" "java" "/opt/jdk1.8.0_202/bin/java" 1 \
--slave /usr/bin/javac javac /opt/jdk1.8.0_202/bin/javac \
--slave /usr/bin/javaws javaws /opt/jdk1.8.0_202/bin/javaws \
--slave /usr/bin/jar jar /opt/jdk1.8.0_202/bin/jar

alternatives --config java`
```
#### Install Presto

reference: https://prestodb.io/docs/current/installation/deployment.html

1. Download presto at https://prestodb.io

2. unpack presto server

    `tar xvzf presto-server-0.233.1.tar.gz -C /opt`

3. create directory ที่ทุกเครื่อง
    ```
    mkdir -p /var/presto/data
    mkdir -p /opt/presto-server-0.233.1/etc/catalog/
    ```

4. create configuration file  etc/node.properties 
    `vi /opt/presto-server-0.233.1/etc/node.properties`

    ```
    node.environment=production
    node.id=ffffffff-ffff-ffff-ffff-ffffffffffff
    node.data-dir=/var/presto/data
    ```
    **remark** node.id is UUID can generate from command **'uuidgen'** and must unique in every node can


5. create configuration file  etc/jvm.config
    `vi /opt/presto-server-0.233.1/etc/jvm.config`

   ```
   -server
   -Xmx2G
   -XX:+UseG1GC
   -XX:G1HeapRegionSize=32M
   -XX:+UseGCOverheadLimit
   -XX:+ExplicitGCInvokesConcurrent
   -XX:+HeapDumpOnOutOfMemoryError
   -XX:+ExitOnOutOfMemoryError
   ```
   **remark** i change Maximum memory (-Xmx) to 2G for fit with my VM.

6. create configuration file  **etc/config.properties**

    6.1 for only **coordinator** (specific right **uri** and **memory** suitable with your resource)

    `vi /opt/presto-server-0.233.1/etc/config.properties`

    ```
    coordinator=true
    node-scheduler.include-coordinator=false
    http-server.http.port=8080
    query.max-memory=2GB
    query.max-memory-per-node=1GB
    query.max-total-memory-per-node=1GB
    discovery-server.enabled=true
    discovery.uri=http://namenode1:8080
    ```

    6.2 for all **worker** (specific right **uri** and **memory** suitable with your resource)

    ```
    coordinator=false
    http-server.http.port=8080
    query.max-memory=1GB
    query.max-memory-per-node=500MB
    query.max-total-memory-per-node=500MB
    discovery.uri=http://namenode1:8080
    ```

7. create configuration file **etc/log.properties**

    `vi /opt/presto-server-0.233.1/etc/log.properties`

    `com.facebook.presto=INFO`

    ***Remark  configuration file on topic 4,5 and 7 use in all node.***

8. create configuration file for jmx connector

    `vi /opt/presto-server-0.233.1/etc/catalog/jmx.properties` 

    `connector.name=jmx`

9. create configuration file for hive connector (specific right **uri** and **path of hadoop configuration file**)

    `vi /opt/presto-server-0.233.1/etc/catalog/hive.properties`

    ```
    connector.name=hive-hadoop2
    hive.metastore.uri=thrift://namenode1:9083
    hive.config.resources=/etc/hadoop/conf/core-site.xml,/etc/hadoop/conf/hdfs-site.xml
    ```

10. try to run presto with **'launcher run'** (it's easy to debug) at all node, coordinator node first 

    `/opt/presto-server-0.233.1/bin/launcher run`

11. if above presto working, for production ran below command  

     `/opt/presto-server-0.233.1/bin/launcher start`

12. Using

     `./presto --server namenode1:8080 --catalog hive --schema default`

13. Web Connector for Tableau
     http://namenode1:8080/tableau/presto-connector.html