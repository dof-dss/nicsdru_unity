#!/bin/bash

clear

echo "Warming up unity sites with HTTrack ..."

httrack https://www.independentreviewofeducation.org.uk// -c5R2b0s0aqzvp0C0r3F "HTTrack cache builder" "-*.jpg" "-*.css" "-*.js" "-*.png" "-*.gif" "-*.jpeg" "-*.ico" "-*/type/*" "-*/topic/*" "-*/date/*" "-*page=*"

echo "---------------------------------------------------"

httrack https://www.nipolicingboard.org.uk// -c5R2b0s0aqzvp0C0r3F "HTTrack cache builder" "-*.jpg" "-*.css" "-*.js" "-*.png" "-*.gif" "-*.jpeg" "-*.ico" "-*/type/*" "-*/topic/*" "-*/date/*" "-*page=*"

echo "---------------------------------------------------"

httrack https://www.pbni.org.uk// -c5R2b0s0aqzvp0C0r3F "HTTrack cache builder" "-*.jpg" "-*.css" "-*.js" "-*.png" "-*.gif" "-*.jpeg" "-*.ico" "-*/type/*" "-*/topic/*" "-*/date/*" "-*page=*"

echo "---------------------------------------------------"

httrack https://www.investnireview.org.uk// -c5R2b0s0aqzvp0C0r3F "HTTrack cache builder" "-*.jpg" "-*.css" "-*.js" "-*.png" "-*.gif" "-*.jpeg" "-*.ico" "-*/type/*" "-*/topic/*" "-*/date/*" "-*page=*"

echo "---------------------------------------------------"

httrack https://www.attorneygeneralni.gov.uk// -c5R2b0s0aqzvp0C0r3F "HTTrack cache builder" "-*.jpg" "-*.css" "-*.js" "-*.png" "-*.gif" "-*.jpeg" "-*.ico" "-*/type/*" "-*/topic/*" "-*/date/*" "-*page=*"

echo "---------------------------------------------------"

httrack https://www.cscsreviewni.net// -c5R2b0s0aqzvp0C0r3F "HTTrack cache builder" "-*.jpg" "-*.css" "-*.js" "-*.png" "-*.gif" "-*.jpeg" "-*.ico" "-*/type/*" "-*/topic/*" "-*/date/*" "-*page=*"

echo "---------------------------------------------------"

httrack https://www.pcsps.org// -c5R2b0s0aqzvp0C0r3F "HTTrack cache builder" "-*.jpg" "-*.css" "-*.js" "-*.png" "-*.gif" "-*.jpeg" "-*.ico" "-*/type/*" "-*/topic/*" "-*/date/*" "-*page=*"

echo "Warming complete.:"
