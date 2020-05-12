## UCLH VIE Monitoring

This app was built during the first wave of the COVID-19 pandemic to enable daily monitoring of flowrates through the VIEs at UCLH and NHNN. 

Data is pulled from the BOC Online Delivery Planning Center. This is automated at 0745 every day using a tagui script that runs headless on the server:

<pre><code>https://dpc.boc.com/public/Welcome.aspx 
type //*[@id="username"] as UCLHNHS
type //*[@id="passwd"] as Oxygen@2
click //*[@id="submit1"]
click //*[@id="PageContent"]/form/div[1]/div/input
click /html/body/div[4]/div/div/div[1]/div/input
click /html/body/div[8]/div/div/div/div/input
click /html/body/div[10]/div/div/div/div/input
py begin
from datetime import datetime, timedelta
d = datetime.today() - timedelta(days=30)
print(d.strftime("%-d-%b-%y"))
py finish
select //*[@id="select3"] as `py_result`
click //*[@id="DownloadButton"]</code></pre>

The app refreshes the data at 0750. Alternatively, data can be manually downloaded from the BOC DPC Online and then uploaded using the file upload tool at the bottom of the page.

An automated email is sent at 0800 with a breakdown of average flowrates from the previous 5 days. The recipient list needs to be added in as config/recipients.php

This app is built using:
* [Laravel](https://github.com/laravel/laravel)
* [TagUI](https://github.com/kelaberetiv/TagUI)
* [Highcharts](https://github.com/highcharts/highcharts)
* [Dropzone](https://github.com/enyo/dropzone)
