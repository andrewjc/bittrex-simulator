
"C:\programs\mongodb\mongoimport" --db bittrex --collection ticker --type csv --columnsHaveTypes --fields "timestamp.int32(),open.double(),high.double(),low.double(),close.double(),volumebtc.double(),volumecurrency.double(),weightedprice.double()" --file tradedata.txt
