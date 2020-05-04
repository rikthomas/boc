https://dpc.boc.com/public/Welcome.aspx

type //*[@id="username"] as UCLHNHS

type //*[@id="passwd"] as Oxygen@2

click //*[@id="submit1"]

click //*[@id="PageContent"]/form/div[1]/div/input

click /html/body/div[8]/div/div/div/div/input

click /html/body/div[10]/div/div/div/div/input

py begin
from datetime import datetime, timedelta

d = datetime.today() - timedelta(days=14)

print(d.strftime("%-d-%b-%y"))
py finish

select //*[@id="select3"] as `py_result`

click //*[@id="DownloadButton"]