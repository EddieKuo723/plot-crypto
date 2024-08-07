<p align="center">  
  <img src="cover.png" max-width="600">
</p>

[![update-price-image](https://github.com/EddieKuo723/plot-crypto/actions/workflows/Update_Image.yml/badge.svg?event=schedule)](https://github.com/EddieKuo723/plot-crypto/actions/workflows/Update_Image.yml)

<h1 align="center">Plot Cryptocurrency</h1>

> 📈 Generate a real-time image displaying the chart of cryptocurrency prices.

## Introduction

This tool provides a seamless solution for generating real-time price charts for seven major cryptocurrencies. With support for Bitcoin (BTC), Ethereum (ETH), Litecoin (LTC), Monero (XMR), Zcash (ZEC), and Bitcoin Cash (BCH), users can track price movements and trends with ease. Whether you're a cryptocurrency enthusiast, trader, or developer, this project offers valuable insights into the dynamic world of digital assets.

## Cache Configurations
`CACHE_SECS`: image cache expiry time in seconds, default to 60 in Dockerfile
```
FROM php:7.2-apache
COPY . /var/www/html/
WORKDIR /var/www/html/

EXPOSE 80
ENV CACHE_SECS=60
```

## Deployment
```
git clone https://github.com/EddieKuo723/plot-crypto.git
docker-compose up -d
```
Visit http://127.0.0.1:5000/, and enjoy it! ✅
<br />
<br />
Set Requests Parameter for different coin:
http://localhost:5000/plotBinance.php?coin=ETH

## Built With
* [Poloniex API](https://docs.poloniex.com/#returnchartdata)
* [Binance API](https://github.com/binance/binance-spot-api-docs/blob/master/rest-api.md#klinecandlestick-data)
* [Docker Compose](https://docs.docker.com/compose/)
* [Composer](https://getcomposer.org/)
* [predis](https://github.com/nrk/predis)
* [Roboto Fonts](https://fonts.google.com/specimen/Roboto)



## Author

**EddieKuo723** © [EddieKuo723](https://github.com/EddieKuo723), Released under the Apache License.<br>
