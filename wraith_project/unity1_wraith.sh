wraith capture configs/attorneygeneral_capture.yaml > output.txt
wraith capture configs/cscsreview_capture.yaml >> output.txt
wraith capture configs/employmenttribunals_capture.yaml >> output.txt
wraith capture configs/fiscalcommissionni_capture.yaml >> output.txt
wraith capture configs/investni_capture.yaml >> output.txt
wraith capture configs/ire_capture.yaml >> output.txt
wraith capture configs/mah_capture.yaml >> output.txt
wraith capture configs/niao_capture.yaml >> output.txt
wraith capture configs/nifiscalcouncil_capture.yaml >> output.txt
wraith capture configs/nipolicingboard_capture.yaml >> output.txt
wraith capture configs/octf_capture.yaml >> output.txt
wraith capture configs/odscni_capture.yaml >> output.txt
wraith capture configs/pbni_capture.yaml >> output.txt
wraith capture configs/pcsps_capture.yaml >> output.txt
wraith capture configs/uregni_capture.yaml >> output.txt
wraith capture configs/urology_capture.yaml >> output.txt
grep -A3 'failed' output.txt
echo '************'
grep gallery output.txt
