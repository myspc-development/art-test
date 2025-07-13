#!/bin/bash
log="$(wp config path 2>/dev/null)/../debug.log"
if [ -f "$log" ]; then
  echo "Last 20 lines of debug.log:" 
  tail -n 20 "$log"
else
  echo "debug.log not found"
fi
