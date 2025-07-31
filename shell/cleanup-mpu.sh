#!/bin/bash

# Bucket names (update these)
buckets=(
cosmoslab
mabbank
preventplastics
amilife
)

# Log file
logfile="s3-cleanup.log"

# Date and time for logging
echo "Cleanup started at $(date)" | tee -a "$logfile"
echo "=============================================" | tee -a "$logfile"

# Loop through buckets
for bucket in "${buckets[@]}"; do
    echo "Processing bucket: $bucket" | tee -a "$logfile"
    
    # List multipart uploads and process each one
    uploads=$(s3cmd -c ~/s3config/.s3-dream multipart s3://$bucket/ | grep -E "^202" | awk '{print $2, $3}')

    if [ -z "$uploads" ]; then
        echo "No incomplete uploads found in s3://$bucket/" | tee -a "$logfile"
    else
        echo "Incomplete uploads found, attempting cleanup..." | tee -a "$logfile"
        
        # Loop through each upload and abort it
        while IFS=' ' read -r object upload_id; do
            echo "Aborting upload for object: $object with UploadId: $upload_id" | tee -a "$logfile"
            
            # Use s3cmd's abortmp command to cancel the multipart upload
            s3cmd -c ~/s3config/.s3-dream abortmp "$object" $upload_id >> "$logfile" 2>&1
            
            if [ $? -eq 0 ]; then
                echo "Successfully aborted upload for: $object with UploadId: $upload_id" | tee -a "$logfile"
            else
                echo "Failed to abort upload for: $object with UploadId: $upload_id" | tee -a "$logfile"
            fi
        done <<< "$uploads"
        
        echo "Cleanup complete for bucket: $bucket" | tee -a "$logfile"
    fi

    echo "---------------------------------------------" | tee -a "$logfile"
done

echo "Cleanup finished at $(date)" | tee -a "$logfile"
