#!/bin/bash

logfile="s3-delete.log"

echo "🗑️ Starting S3 bucket deletions at $(date)" | tee -a "$logfile"
echo "==========================================" | tee -a "$logfile"

buckets=(
totalbots
)

total=${#buckets[@]}
count=1

for bucket in "${buckets[@]}"; do
  echo ""
  echo "🔄 [$count/$total] Deleting objects in s3://$bucket/ ..." | tee -a "$logfile"

  s3cmd -c ~/s3config/.s3-dream del "s3://$bucket/" --recursive --force >> "$logfile" 2>&1
  status_del=$?

  if [ $status_del -eq 0 ]; then
    echo "✅ Objects deleted in s3://$bucket/" | tee -a "$logfile"

    echo "🧹 Removing empty bucket s3://$bucket/ ..." | tee -a "$logfile"
    s3cmd -c ~/s3config/.s3-dream rb "s3://$bucket/" --force >> "$logfile" 2>&1
    status_rb=$?

    if [ $status_rb -eq 0 ]; then
      echo "✅ Bucket s3://$bucket/ removed successfully" | tee -a "$logfile"
    else
      echo "❌ Failed to remove bucket s3://$bucket/ (exit code $status_rb)" | tee -a "$logfile"
    fi

  else
    echo "❌ Failed to delete objects in s3://$bucket/ (exit code $status_del)" | tee -a "$logfile"
  fi

  echo "------------------------------------------" | tee -a "$logfile"
  ((count++))
done

echo ""
echo "🏁 All deletions and bucket removals finished at $(date)" | tee -a "$logfile"