import ApiSCSE
import sys
import pprint
import os
import time

apiscse = ApiSCSE.ApiSCSE()

API_USER = '__LOGIN_EMAIL__'
API_KEY = '__API_CREDENTIALS__'
KEYWORD_FILE = 'fortune-500.txt'
RESULTS_PER_KEYWORD = 100
LANGUAGE = 'English'
COUNTRY = 'Default'
PRIORITY = 10
JOBNAME = 'Demo Job'

MODE = 'http'

if MODE == 'http' : print '<pre>'

res = apiscse.initi(API_USER, API_KEY)
if not res:
	print "Error during API initialization: "
	print apiscse.getError()
	sys.exit(1)

license = apiscse.getLicense()
credits = apiscse.getCredits()
jobs = apiscse.getJobs()
jobs_unfinished = 0
jobs_finished = 0
jobs_running = 0

for job in jobs:
	if int(job['progress']) == 100:
		jobs_finished += 1
	elif int(job['start']) == 1:
		jobs_running += 1
	else:
		jobs_unfinished += 1

print 'SCSE license active: %s' % license['name']
print 'SCSE wallet: %s Credits' % credits['credit']
print 'Jobs: %d unfinished jobs, %d running jobs, %d finished jobs' % (jobs_unfinished, jobs_running, jobs_finished)

if float(credits['credit']) < 0.6:
	print 'Insufficient Credits in wallet\n'
	sys.exit(1)

job = apiscse.getJob(JOBNAME)
print job
if not job:
	print "Creating job " + JOBNAME + ": ";
	job = apiscse.createJob(JOBNAME, LANGUAGE, COUNTRY, RESULTS_PER_KEYWORD, PRIORITY)
	if job:
		print "success\n"
	else:
		print "failure\n"
		if apiscse.getLastResponse().has_exception : pprint.pprint(apiscse.getLastResponse().exceptions)
		sys.exit(1)


if int(job['start']) == 0:
	if not os.path.exists(KEYWORD_FILE):
		print 'File ' + KEYWORD_FILE + ' does not exist\n'
		sys.exit(1)
	temp = open(KEYWORD_FILE, 'rU').readlines()
	keywords = []
	for i in temp:
		keywords.append(i.strip('\n'))
	res = apiscse.modify_job(JOBNAME, keywords, apiscse.MODE_REPLACE, LANGUAGE, COUNTRY, RESULTS_PER_KEYWORD, PRIORITY);
	if not res:
		print 'Modification of Job failed\n'
		if apiscse.getLastResponse().has_exception : pprint.pprint(apiscse.getLastResponse().exceptions)
		sys.exit(0)

	print 'Job starting cost: %d Credits\n' % res['credit_cost']

if int(job['start']) == 0:
	pass
res = apiscse.start_job(JOBNAME)
if not res:
	started = 0
	for exception in apiscse.getLastResponse().exceptions :
		if exception['error_id'] == 10008:
			started = 1
			break
	if not started:
		print 'Job ' + JOBNAME + ' could not be started\n'
		sys.exit(1)
	print 'Job ' + JOBNAME + ' was already started\n'
else:
	print 'Job ' + JOBNAME + ' has been started\n'


latest_page = 0
job = apiscse.getJob(JOBNAME)

original_estimated_hours = float(job['estimated_hours_left'])
start_time = time.time()
end_time = start_time + float(original_estimated_hours) * 3600 * 3
once = 0
total_organic = 0
total_creative = 0

while (not once) or (time.time() < end_time):
	print "Waiting for results, estimated time left: %.2f hours ; Progress: %.2f..\n" % (float(job['estimated_hours_left']), float(job['progress']))
	if MODE == 'http' : sys.stdout.flush()
	time.sleep(5)
	job = apiscse.getJob(JOBNAME)
	total_pages = int(job['total_pages'])
	result_pages = int(job['pages'])
	while result_pages > latest_page:
		print 'Downloading new result page ' + str((latest_page + 1)) + '\n'
		results = apiscse.getResults(JOBNAME, latest_page + 1);
		for key in results:
			print key
		for result in results['keyword_results']:
			print 'Keyword: %s - Organic results: %d, Creative results: %d\n' % (result['keyword'], result['count_organic'], result['count_creative'])
			total_organic += result['count_organic']
			total_creative += result['count_creative']
			print 'Organic results:\n'
			for list1 in result['results_organic']:
				print '\tURL: %s' % list1['url']
				print '\tTitle: %s' % list1['title']
				print '\tDescription: %s' % list1['description']
				if list1['sitelinks']:
					for sitelink in list1['sitelinks']:
						print '\t\tURL: %s' % sitelink['url']
						print '\t\tTitle: %s' % sitelink['title']
						print '\t\tDescription: %s' % sitelink['description']
			print "\n"
		try:
			if result['creative'] > 0:
				print 'Advertisements:'
				for list1 in result['results_creative']:
					print '\tURL: %s' % list1['url']
					print '\tTitle: %s' % list1['title']
					print '\tDescription: %s' % list1['description']
					if list1['sitelinks']:
						for sitelink in list1['sitelinks']:
							print '\t\tURL: %s' % sitelink['url']
							print '\t\tTitle: %s' % sitelink['title']
							print '\t\tDescription: %s' % sitelink['description']
					print '\n'
			print '\n'
		except:
			pass
		latest_page += 1
	if total_pages == latest_page : break
	once+=1

hours_passed = round((time.time() - start_time) / 3600, 2)
print '\n'
print 'Scraping of %d pages finished successfully\n' % latest_page
print 'Received %d organic results and %d ads\n' % (total_organic, total_creative)
print 'Estimated time: %s hours, Real time: %d hours\n' % (str(original_estimated_hours), hours_passed)

if (hours_passed != 0.00) and (time.time() >= end_time):
	print 'ERROR: Timeout while waiting for results\n'
