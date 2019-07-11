# Scraping Google for Fun and Profit

![](https://www.paypalobjects.com/de_DE/i/scr/pixel.gif) This information and source code is provided for free. Anyway a donation would be appreciated.

written 2010 by Justone \[justone(at)squabbel.com\], updates 2011, rewrite 2012-2014  
update from 16th Nov. 2010: The scraper source code is now compatible with the new google design (instant, previews, etc)  
update from 13th Dec. 2011: The scraper source code is now compatible with Google design changes (span removed)  
update 2012: some bugfixes  
update 2012: A better google scraper was written this year, check out the [Google Rank Checker](http://google-rank-checker.squabbel.com)  
update 2017: I have been developing backend-code for a huge scraping service at [Scraping services](http://scraping.services)

I've a great update to all my readers! I spent weeks and developed a much more advanced project, free again!
Instead of billing for my work (and I had more than a few requests to write custom code) I added donation buttons and hope they will be used.

**Make sure to check out the successor of this code:
the new (2012-2016) Open Source **

# [Google Rank Checker (PHP)](http://google-rank-checker.squabbel.com)

Google SERP scraping is an often required task for SEO experts and Internet professionals.
By scraping it is possible to monitor ranking positions (SERP), the PPC market,
link popularity and much more.
No matter if you offer scraping as an SEO service, embed into your website,
or if you require it for your own projects: You need important knowhow to succeed.

I am providing you the key-knowhow about SERP scraping, focused on Google: the largest search engine.
You will find important hints and a complete multi-page google search engine scraper
written in PHP with private proxy API support for proxy rotation!

### What happens if you scrape Google ?

Google is the largest scraper on the world but they do not allow scraping of their own pages.
Without a lot of experience and knowhow it can be a hard task to get anything out of them.
Google uses a number of techniques to detect automated access and to prevent it.

**When Google detects scraping activity this is going to happen**:
**1.** When accessing Google, you can be warned about something "dangerous" going on.
You will see a warning about a possible Virus or Trojan on your computer.
**2.** If you continue scraping Google they will now throw in their first block.
You will again see the virus message, this time you need to enter a Captcha to continue.
The Captcha will create an authentication cookie that allows you to continue.
**3.** Now Google uses larger weapons: They will block your IP temporarily. ("Google blocked your ip temporarily")
It can last from minutes to hours, you immediately need to stop your current scraping and change code/add IPs.
**4.** If you scrape google again you will be banned for a longer time.

**How does Google detect scraping ?**
That's the key question and not too hard to find out:
Google mainly watches for \* the IP address: the IP is the only identification sign of a user they use \* keyword changes: normal users don't look for many keywords in a short time \* frequency: every access to google is matched with allowed access patterns

### Hints for scraping Google and avoiding detection

- First of all you need a reliable proxy source to be able to change your IP-Address.
  Of course the proxies have to be high anonymous, they should be fast and there
  should have been no previous abuse against google.
  I can personally recommend the private proxy solution at [www.seo-proxies.com](http://www.seo-proxies.com) but you
  can use any proxy solution as long as it delivers quality IPs that have not been used for Google access before.
  For continued scraping activity you should use between 50 and 150 proxies depending on the
  average resultset of each search query. Some projects might require even more.
  **Important: Do not scrape with too few IPs and never continue scraping when Google did detect you!**
- Make sure you clear Cookies after each IP change or disable them completely
- Set the number of search results to the maximum (100) by adding &num=100 to the search URL
- Do not use threads (multiple scraping processes at the same time) if not required. You can scrape millions
  of results per day without using threads.
- Append other keywords to your main search, google will make it hard to get more than 1000
  results (for a particular topic) but you can actually get almost all URLs.
- Change your IP at the correct moments. This is a key element of your scraping success!
  When getting 300-1000 results per keyword you should rotate your IP after each keyword change.
  When getting less than 300 results you can scrape a few keywords with one IP but you might
  have to add a delay (sleep() ) or you have to increase the number of proxies.
- In case you receive a virus/captcha warning it's time to immediately stop your actions.
  Captcha means : Your scraping has been detected !
  Add more proxies, if you already use 100 or more you might have to use another source
  of IPs (see my recommendation for private proxies above, it is unlikely you can find a better source).
  If you do your job right you can scrape Google 24 hours a day without being detected.
- For reliable scraping you need to avoid any sort of black or graylisting, do not scrape more than 500 requests per 24 hours (well spread) per IP address.

### For your use and customization: an advanced Google scraper written in PHP for web or console usage

This source is free for your fun and profit, you can change everything except the first free
commented lines.
This script includes:
1\. Automated proxy rotation (using the API seo-proxies.com, a reliable private proxy service)
If you have own reliable proxies you need to adapt that part, try use clean and fast proxies for good results!
If you have a license at www.seo-proxies.com then all you need to do is to change the
"USERID" and "API-PASSWORD" variables at the top of the scraper.php script to match your license.
2\. Automated scraping of all google result pages from a specific search result
3\. Usage of sub-keywords to increase the number of possible results
4\. Automated detection and removal of advertisements
5\. Storage of the scraped results in an array, displaying it on demand as HTML text or normal text

What you should consider to do is to add database support for storing results and managing keywords!
For professional projects PHP is well suited but you should use the scraper as console script for best reliability.
Download the two source code files here:  
[scraper.php](scraper.php)  
[functions.php](functions.php)

**Make sure to also check our the highly advanced and new (since 2012) free** [Google Rank Checker](http://google-rank-checker.squabbel.com), opensource PHP and much better than this project

# [Scraping as a service](https://scraping.services/?faq&chapter=why_scraping_service)

During the past two years I was helping a scraping startup to get their scraping up to highest levels.
From all my work this is the most advanced one, sadly I can not share the backend code but I had the opportunity to write part of their [Scraping API](http://scraping.services/?api&chapter=Source+Code) which is Open Source licensed.

It's not the easiest task to decide the approach for your project.
Often, especially for small to medium workloads a self made solution is the very best way to go.
For large amounts or if you do not like the need to regularly maintain your (or my :-) ) source code to keep it working it might be the best to rely on such a service.
You can also go both routes so you can always switch depending on which one is better for you right now (depending on cost and amount of results).
