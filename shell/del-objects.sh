#!/bin/bash

logfile="s3-delete.log"

echo "🗑️ Starting selective object deletions at $(date)" | tee -a "$logfile"
echo "=================================================" | tee -a "$logfile"

buckets=(
amgmyanmar
cdsg
chinlandfc
healthpointmedicalcenter
jyt-travel
bestsheltermyanmar
foreverweddingstudio
primehubmyanmar
tielbo
tourshalom
aureumpalacehotel
betterversion
hotelshwegonedaing
landcoregroup
pledgeconsulting
preciousholidaytravels
proparagon
travelnow
acaeschool
durablepeace
gloryassumptionspace
gwe
kkmyanmar
mabladycard
monhluttaw
myanmardamsel
realslim
sas
winelink
yais
)

prefixes=(
  backup_2019*
  backup_2020*
  backup_2021*
  backup_2022*
  backup_2023*
  backup_2024*
)

bucket_total=${#buckets[@]}
prefix_total=${#prefixes[@]}
bucket_count=1

for bucket in "${buckets[@]}"; do
  echo ""
  echo "📦 [$bucket_count/$bucket_total] Processing bucket: s3://$bucket/" | tee -a "$logfile"

  prefix_count=1
  for prefix in "${prefixes[@]}"; do
    echo "  🔄 [$prefix_count/$prefix_total] Deleting s3://$bucket/$prefix ..." | tee -a "$logfile"

    s3cmd -c ~/s3config/.s3-dream del "s3://$bucket/$prefix" --force >> "$logfile" 2>&1
    status=$?

    if [ $status -eq 0 ]; then
      echo "  ✅ Successfully deleted s3://$bucket/$prefix" | tee -a "$logfile"
    else
      echo "  ❌ Failed to delete s3://$bucket/$prefix (exit code $status)" | tee -a "$logfile"
    fi

    ((prefix_count++))
  done

  echo "----------------------------------------------" | tee -a "$logfile"
  ((bucket_count++))
done

echo ""
echo "🏁 All prefix deletions completed at $(date)" | tee -a "$logfile"
