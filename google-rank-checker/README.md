# Scraping Google Ranks for Fun and Profit

written 2012, by Justone \[justone(at)squabbel.com\]  
update and bugfixes: 4/28/2012  
adapted to new Google design: 12/06/2012  
added filter configuration to support exact human results: 12/07/2012  
bugfix in functions.php: 6/13/2013  
rewrite of Google parser: 3/19/2014  
small fixes: 6/19/2016  
Appended information about [scraping.services](http://scraping.services) 1/5/2017

Using my experience on scraping and backend IT solutions I had written the free [Advanced Google Scraper](http://google-scraper.squabbel.com) in 2009.
The source code was offered for free online and received feedback every week.
In the past years I received a lot of positive comments and many questions regarding scraping. Usually for professional projects.
The new scraper/rank checker here is a complete rewrite of the original one with a more stable html parser and better inner structures.

Due to the complete lack of experience for such projects by average developers I decided to make this sort of challenging development my new profession, if you require customization just contact me.
I can help with IP addresses, development, hosting, server management and whatever else you require to run such a challenging project professionally.
However it is not too hard to get everything up and running if you or your developer has a bit experience with servers and PHP.

![](https://www.paypalobjects.com/de_DE/i/scr/pixel.gif)

[Scraping through a service (2017)
](https://scraping.services/?faq&chapter=why_scraping_service)
===========================
A completely new option to consider for scraping Google or Bing is another project I have been working on for a customer of mine.
[Scraping.services](http://scraping.services) is not completely open source anymore but it is a high volume scraping solution that takes almost all tasks from your shoulders.
In many cases it will be a more cost efficient solution, in some cases you might want to go both routes to reduce your dependency on one approach (higher reliability).

In any case I would suggest reading this website, you will learn a lot of tricks and difficulties about scraping Google (which also applies on Bing and others)

**The new and free Google Rank Checker can solve these requirements:**

- Scraping accurate (specific and general) website ranks for multiple keywords, going through multiple result pages (if required)
- Country and language specific search results (without using local IP addresses!)
- Filter setting support for accurate results
- IP/Proxy management and delay mechanisms to avoid detection, gray and blacklistings
- Local file based serp-caching with configurable "resolution" to avoid unnecessary requests and speed up later runs
- Full support of the seo-proxies.com API
- Modular source code design which makes it a LOT easier to adapt the source for own requirements
- Pure PHP code, console optimized and with nearly no dependencies/requirements
- Preparation for multithreaded usage

I will now cover the principles of scraping and go into detail about some of the rank-checker's features, some information will overlap with my [Advanced Google Serp Scraper](http://google-scraper.squabbel.com) website

Scraping search engines is a serious task. These days companies invest a lot of money and effort for organic and paid search engine traffic.
This project concentrates on the organic search engine rank and it focuses only on the Google results. Google is still so far ahead compared with search engines that I did not invest the time to parse the competition yet.
Mainly for larger SEO projects it is also important to know the keyword rankings of local country results, so I invested the time to analyze this part also and learn what is required to make it possible.

## Accurate scraping results

Sounds simple but actually there are multiple traps one can fall into that result in inaccurate search results.
That might be unimportant for pure link scraping requirements but very important when it comes to rank analysis.
Some Google parameters can affect ranking results, bad IPs/proxies or too many requests can also change ranking results.
The free Rank Checker will provide accurate ranking results, filter out advertisements and is able to parse Rank, URL, Title and Description of each result.

## Country and language specific scraping results

At first I thought it is impossible to provide country/language specific result pages without using IPs from those countries.
I ran many detailed experiments during development and using the finished Google Rank Checker, finally I learned that it is possible to provide accurate location specific results.
As usual things are not as easy as one might expect!
Google provides more than 160 different languages and domains, often more than one official language is provided for one country and each language can provide different ranking results.
Each domain will provide different results. So there is not a clear "ranking list" for one country, there can be multiple such rankings that are all authentic.

The Google Rank Checker will require you to provide two codes, one for the language and one for the country. It is able to provide a list of all codes/languages/countries.
The default configuration is "Google Worldwide" and "english" which produces the typical US ranking results.

For verification I used four fresh browser installations of Google Chrome with local IP addresses from USA, UK, Germany and Austria.
At the same time I ran the Google Rank Checker using a seo-proxies.com license and configured it for each of the four countries and languages.
The results were very similar with small variations of ranking positions, of course that is a concern and required a deeper analysis!
Using specific google parameters (please see the php source code for details) I was able to receive good ranking results for each country.
Warning: I've also seen some very strange ranking results when using lower quality IP addresses from heavily used US datacenters. I used seo-proxies.com for the real tests which provides good IP quality.

As I had multiple german servers and access to a german DSL (residential) computer at that time (for another project) I used Google Germany for the main test.
The results showed similar tiny ranking changes from computer to computer, so even within one country the ranking results are not always exactly the same.
In most cases one or two sites that are next to each other interchange the ranking on a result page.
This might be because Google ranks are not always completely synchronized among all servers, or another influence I've not yet got my grip on.

Summary on local Google ranking results:
The free Google Rank Checker will provide as accurate results as I was able to receive when using local IP addresses.
A small inaccuracy is always possible, even when using different IPs from the country itself.

## IP/Proxy management

When scraping Google it is essential to avoid detection. Google does not want to be abused from thousands of people as this could have
an impact on their servers (and they don't like to share their database with us).
Avoiding detection is not a hard task if you are doing things right.
a) do not push out more requests than 20 per hour per IP address
b) try to spread the requests evenly (don't push 20 in 1 minute and wait 59 minutes)
c) avoid cookies at all, they are not required
d) rotate your IP address for each different keyword (you can and should request multiple pages of one keyword with the same IP)
The free Google Rank Checker includes IP management functions which will "remember" the IP usage (also between application runs) and takes the number of available IPs into account when adding delays between requests

## Local file based caching

Mainly during the development of scrapers one challenge is to keep the IPs (often a smaller number of IPs than in production use) in good quality.
Often the programmer has to do a large number of test runs, each time the scraper will access Google .. using up the IPs.
The free Google Rank Checker contains caching functions that will store the parsed page as serialized "object" in a directory.
This way the Scraper can be run as often as one likes while tweaking program functions or the parser, if it already scraped the page it will not access Google again.
The default "resolution" is 24 hours, this can of course be changed.
Optional the caching can be disabled or forced.

## The seo-proxies.com API

The free Google Rank Checker is supporting the seo-proxies.com API!
seo-proxies.com is a high quality proxy service which solves a major task when scraping, getting a reliable and trustworthy source of IPs.
Using low quality IPs can result in a lot of troubles, Google often knows those IPs already due to frequent previous abuse.
SEO-Proxies.com also features a special API function that is used by the Rank Checker, it provides Google domains and language codes for country/language specific ranks.

Of course it is possible to replace those functions in the free Rank Checker with an own solution, however I can recommend the use of seo-proxies.com for production environments and serious projects.

## Modular source code design

As always, if someone is in a rush developing something it often does not look nice and is hard to see through at a later time.
That was the case with the Advanced Google Scraper from my last article.
The Google Rank Checker is based on the Advanced Google Scraper but heavily cleaned up and should be much easier to see through for other programmers.

## Pure PHP code

For scraping projects PHP is a nice programming language, it allows on-the-fly changes and tweaks to adapt to problems and can run long-term and reliable as console application.
PHP as programming language is mainly focused on web programming, this also means it comes with many functions very useful for scraping tasks.
In our case we use the powerful libCURL API to send our HTTP requests and the DOM (document object model) functions to parse the results.
Of course you can also launch the Google Rank Checker through a webserver but for production use it is recommended to run it as console script.
The code was tested on PHP 5.2.6 but should be compatible with most PHP 5 versions.

## Multithreading

For larger scaled scraping it is often required to run multiple threads at the same time.
The code already contains some comments where small changes would be required to make it multi-threading compatible.
Anyway, for most projects you will be fine with a single instance.

When using seo-proxies.com it is possible to request additional proxy processes for multi-threading, alternatively you can use multiple accounts.

## Hints for scraping Google and avoiding detection

- First you need a reliable proxy source to be able to change your IP-Address.  
  Of course the proxies have to be high anonymous, they should be fast and there should have been no previous abuse against Google.  
  I can personally recommend the built in private proxy solution at [www.seo-proxies.com](http://www.seo-proxies.com) but you  
  can try another proxy solution as long as it delivers quality IPs that without abusive history.  
  For continued scraping activity you should use between 50 and 150 proxies depending on the  
  average resultset of each search query. Some projects might require even more.  
  If you wish to start with a lower number of IPs during development you should still at least get 5 IPs, better 10 or 20. You will need them!

- **Never** continue scraping when Google did detect you! You need automated detection and abort routines like in the free Google Rank Checker.
- Make sure you clear Cookies after each IP change or disable them completely, with libCURL cookies are ignored by default
- Do not change the number of search results from 10 to a higher number if you wish to receive accurate ranks from Google
- Do not use threads (multiple scraping processes at the same time) if it is not really required. That just makes things more complicate.
- In the case you receive a virus/captcha warning, it's time to stop immediately.  
  Captcha means : Your scraping has been detected !  
  Add more proxies, if you already use 100 or more you might have to use another source  
  of IPs (see my recommendation for private proxies above, it is unlikely you can find a better source).  
  If you do your job right you can scrape Google 24 hours a day without being detected.
- For reliable scraping you need to avoid any sort of black and graylisting, do not scrape more than 20 requests per hour per IP address.

## The heart of my article: The free Google Rank Checker, written in PHP for web or console (recommended) usage

This source is free for your fun and profit, you can adapt the source to your requirements.
Please make sure to read the short license agreement on top of the source code.

This script includes:

- Scraping accurate (specific and general) website ranks for multiple keywords, going through multiple result pages (if required)
- Country and language specific search results (without using local IP addresses!)
- IP/Proxy management and delay mechanisms to avoid detection, gray and blacklistings
- Local file based serp-caching with configurable "resolution" to avoid unnecessary requests and speed up later runs
- Full support of the seo-proxies.com API
- Modular source code design which makes it a LOT easier to adapt the source for own requirements
- Pure PHP code, console optimized and with nearly no dependencies/requirements
- Preparation for multithreaded usage

For professional projects PHP is well suited but you should use the scraper as console script for best reliability.

Requirements: \* PHP 5.2 (5+ should do it) with libCURL and DOM support \* An SEO-Proxies.com license for high quality IPs and the Google country API \* "write rights" in the local directory, the script will create a working directory for local storage

![](https://www.paypalobjects.com/de_DE/i/scr/pixel.gif)

Download the three source code files here:  
[simple_html_dom.php](simple_html_dom.php)  
[google-rank-checker.php](google-rank-checker.php)  
[functions.php](functions.php)
