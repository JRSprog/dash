#!/bin/bash
DATE=$(date +%Y%m%d)
mysqldump -u username -p yourdb purchases_archive purchase_items_archive > /backups/archives_$DATE.sql
gzip /backups/archives_$DATE.sql
find /backups/ -name "archives_*.sql.gz" -mtime +30 -delete