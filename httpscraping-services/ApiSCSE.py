import pycurl
import json
from io import BytesIO
from urllib import urlencode

class ApiException:

	def __init__(self):
		pass

	exception_text = None
	level = None
	unixtime = None
	error_id = None

class ApiResponse:

	def __init__(self):
		pass

	answer = None
	exceptions = []
	has_exception = None

class ApiSCSE:

	def __init__(self):
		pass

	MODE_REPLACE = 'replace'
	MODE_APPEND = 'append'

	API_COUNTRIES = ['Default', 'Afghanistan', 'Albania', 'Algeria', 'American Samoa', 'Andorra', 'Angola', 'Anguilla', 'Antarctica', 'Antigua and Barbuda', 'Argentina', 'Armenia', 'Aruba', 'Australia', 'Austria', 'Azerbaijan', 'Bahamas', 'Bahrain', 'Bangladesh', 'Barbados', 'Belarus', 'Belgium', 'Belize', 'Benin', 'Bermuda', 'Bhutan', 'Bolivia', 'Bosnia and Herzegovina', 'Botswana', 'Bouvet Island', 'Brazil', 'British Indian Ocean Territory', 'Brunei Darussalam', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Cambodia', 'Cameroon', 'Canada', 'Cape Verde', 'Cayman Islands', 'Central African     Republic', 'Chad', 'Chile', 'China', 'Christmas Island', 'Cocos (Keeling) Islands', 'Colombia', 'Comoros', 'Congo', 'Congo, the Democratic Republic of the', 'Cook Islands', 'Costa Rica', 'Cote D\'ivoire', 'Croatia', 'Cuba', 'Cyprus', 'Czech Republic', 'Denmark', 'Djibouti', 'Dominica', 'Dominican Republic', 'Ecuador', 'Egypt', 'El Salvador', 'Equatorial Guinea', 'Eritrea', 'Estonia', 'Ethiopia', 'Falkland Islands (Malvinas)', 'Faroe Islands', 'Fiji', 'Finland', 'France', 'French Guiana', 'French Polynesia', 'French Southern Territories', 'Gabon', 'Gambia', 'Georgia', 'Germany', 'Ghana', 'Gibraltar', 'Greece', 'Greenland', 'Grenada', 'Guadeloupe', 'Guam', 'Guatemala', 'Guinea', 'Guinea-Bissau', 'Guyana', 'Haiti', 'Heard Island and Mcdonald Islands', 'Holy See (Vatican City State)', 'Honduras', 'Hong Kong', 'Hungary', 'Iceland', 'India', 'Indonesia', 'Iran, Islamic Republic of', 'Iraq', 'Ireland', 'Israel', 'Italy', 'Jamaica', 'Japan', 'Jordan', 'Kazakhstan', 'Kenya', 'Kiribati', 'Korea, Democratic People\'s Republic of', 'Korea, Republic of', 'Kuwait', 'Kyrgyzstan', 'Lao People\'s Democratic Republic', 'Latvia', 'Lebanon', 'Lesotho', 'Liberia', 'Libyan Arab Jamahiriya', 'Liechtenstein', 'Lithuania', 'Luxembourg', 'Macao', 'Macedonia, the Former Yugosalv Republic of', 'Madagascar', 'Malawi', 'Malaysia', 'Maldives', 'Mali', 'Malta', 'Marshall Islands', 'Martinique', 'Mauritania', 'Mauritius', 'Mayotte', 'Mexico', 'Micronesia, Federated States of', 'Moldova, Republic of', 'Monaco', 'Mongolia', 'Montserrat', 'Morocco', 'Mozambique', 'Myanmar', 'Namibia', 'Nauru', 'Nepal', 'Netherlands', 'Netherlands Antilles', 'New Caledonia', 'New Zealand', 'Nicaragua', 'Niger', 'Nigeria', 'Niue', 'Norfolk Island', 'Northern Mariana Islands', 'Norway', 'Oman', 'Pakistan', 'Palau', 'Palestinian Territory, Occupied', 'Panama', 'Papua New Guinea', 'Paraguay', 'Peru', 'Philippines', 'Pitcairn', 'Poland', 'Portugal', 'Puerto Rico', 'Qatar', 'Reunion', 'Romania', 'Russian Federation', 'Rwanda', 'Saint Helena', 'Saint Kitts and Nevis', 'Saint Lucia', 'Saint Pierre and Miquelon', 'Saint Vincent and the Grenadines', 'Samoa', 'San Marino', 'Sao Tome and Principe', 'Saudi Arabia', 'Senegal', 'Serbia and Montenegro', 'Seychelles', 'Sierra Leone', 'Singapore', 'Slovakia', 'Slovenia', 'Solomon Islands', 'Somalia', 'South Africa', 'South Georgia and the South Sandwich Islands', 'Spain', 'Sri Lanka', 'Sudan', 'Suriname', 'Svalbard and Jan Mayen', 'Swaziland', 'Sweden', 'Switzerland', 'Syrian Arab Republic', 'Taiwan, Province of China', 'Tajikistan', 'Tanzania, United Republic of', 'Thailand', 'Timor-Leste', 'Togo', 'Tokelau', 'Tonga', 'Trinidad and Tobago', 'Tunisia', 'Turkey', 'Turkmenistan', 'Turks and Caicos Islands', 'Tuvalu', 'Uganda', 'Ukraine', 'United Arab Emirates', 'United Kingdom', 'United States', 'United States Minor Outlying Islands', 'Uruguay', 'Uzbekistan', 'Vanuatu', 'Venezuela', 'Viet Nam', 'Virgin Islands, British', 'Virgin Islands, U.S.', 'Wallis and Futuna', 'Western Sahara', 'Yemen', 'Zambia', 'zw']

	API_LANGUAGES = ['Afrikaans', 'Albanian', 'Amharic', 'Arabic', 'Armenian', 'Azerbaijani', 'Basque', 'Belarusian', 'Bengali', 'Bihari', 'Bosnian', 'Breton', 'Bulgarian', 'Cambodian', 'Catalan', 'Chinese (Simplified)', 'Chinese (Traditional)', 'Corsican', 'Croatian', 'Czech', 'Danish', 'Dutch', 'English', 'Esperanto', 'Estonian', 'Faroese', 'Filipino', 'Finnish', 'French', 'Frisian', 'Galician', 'Georgian', 'German', 'Greek', 'Guarani', 'Gujarati', 'Hausa', 'Hebrew', 'Hindi', 'Hungarian', 'Icelandic', 'Indonesian', 'Interlingua', 'Irish', 'Italian', 'Japanese', 'Javanese', 'Kannada', 'Kazakh', 'Kinyarwanda', 'Kirundi', 'Korean', 'Kurdish', 'Kyrgyz', 'Laothian', 'Latin', 'Latvian', 'Lingala', 'Lithuanian', 'Macedonian', 'Malagasy', 'Malay', 'Malayalam', 'Maltese', 'Maori', 'Marathi', 'Moldavian', 'Mongolian', 'Montenegrin', 'Nepali', 'Norwegian', 'Norwegian (Nynorsk)', 'Occitan', 'Oriya', 'Oromo', 'Pashto', 'Persian', 'Polish', 'Portuguese (Brazil)', 'Portuguese (Portugal)', 'Punjabi', 'Quechua', 'Romanian', 'Romansh', 'Russian', 'Scots Gaelic', 'Serbian', 'Serbo-Croatian', 'Sesotho', 'Shona', 'Sindhi', 'Sinhalese', 'Slovak', 'Slovenian', 'Somali', 'Spanish', 'Sundanese', 'Swahili', 'Swedish', 'Tajik', 'Tamil', 'Tatar', 'Telugu', 'Thai', 'Tigrinya', 'Tonga', 'Turkish', 'Turkmen', 'Twi', 'Uighur', 'Ukrainian', 'Urdu', 'Uzbek', 'Vietnamese', 'Welsh', 'Xhosa', 'Yiddish', 'Yoruba', 'Zulu']

	VERSION = 105
	api_login = None
	api_key = None
	initialized = None
	error_msg = None

	license = []

	last_response = None

	def initi(self, login, key):
		self.api_key = key
		self.api_login = login
		self.initialized = True
		ret = self.call('info', 'license_info')
		if not ret:
			return False
		if ret.has_exception:
			self.error_msg = ret.exceptions[0].exception_text
			return False
		self.license = ret.answer['license']
		return True

	def getLastResponse(self):
		return self.last_response

	def getLicense(self):
		if not self.initialized:
			self.error_msg = 'not initialized'
			return False
		return self.license

	def getCredits(self):
		res = self.call('info', 'wallet_info')
		return res.answer['wallet']

	def getJobs(self):
		res = self.call('scrape_google_search', 'get_jobs')
		return res.answer['jobs']

	def getJob(self, jobname):
		res = self.call('scrape_google_search', 'get_job', {'jobname' : jobname})
		if res.has_exception : return False
		return res.answer['job']

	def getResults(self, jobname, page = 1):
		res = self.call('scrape_google_search', 'get_results', {'jobname' : jobname, 'page' : page})
		if res.has_exception : return False
		return res.answer['results']

	def start_job(self, jobname):
		res = self.call('scrape_google_search', 'start_job', {'jobname' : jobname})
		if res.has_exception : return False
		return res.answer['job']

	def remove_job(self, jobname):
		res = self.call('scrape_google_search', 'remove_job', {'jobname' : jobname})
		if res.has_exception : return False
		return res.answer[0]

	def createJob(self, jobname, language = 'English', country = 'Default', results_per_kw = 100, priority = 1):

		if not language in self.API_LANGUAGES:
			print "Error: invalid language %s\n" % language
			return False

		if not country in self.API_COUNTRIES:
			print "Error: invalid country\n"
			return False

		if priority < 0 : return False
		if priority > 100 : return False
		if results_per_kw < 1 : return False
		if results_per_kw > 1000 : return False

		res = self.call('scrape_google_search', 'create_job', {'jobname' : jobname, 'language' : language, 'country' : country, 'job_type' : 'scrape_google_search', 'max_results' : results_per_kw, 'priority' : priority})
		if res.has_exception :
			print res.exceptions
			return False
		return res.answer['job']

	def modify_job(self, jobname, keywords = [], keyword_operation_mode = 'append', language = None, country = None, results_per_kw = None, priority = None):

		if language:
			if not language in self.API_LANGUAGES:
				print "Error: invalid language %s\n" % language
				return False

		if country:
			if not country in self.API_COUNTRIES:
				print "Error: invalid country\n"
				return False

		if priority and priority < 0 : return False
		if priority and priority > 100 : return False
		if results_per_kw and results_per_kw < 1 : return False
		if results_per_kw and results_per_kw > 1000 : return False

		object = {'jobname' : jobname}
		#print 'object:   ' ,object
		if len(keywords):
			object['keyword_operation'] = keyword_operation_mode
			object['keywords'] = keywords

		print 'here', jobname, keywords, keyword_operation_mode,language,country, results_per_kw,priority

		if results_per_kw : object['max_results'] = results_per_kw
		if language : object['language'] = language
		if country : object['country'] = country
		if priority : object['priority'] = priority

		res = self.call('scrape_google_search', 'modify_job', object)
		if res.has_exception : return False

		return res.answer['job'];

	def GetError(self):
		return self.error_msg if self.error_msg else False

	def call(self, job_type, task, arguments = {}):
		if not self.initialized:
			self.error_msg = 'not initialized'
			return False

		self.error_msg = None

		buffer = BytesIO()
		c = pycurl.Curl()

		c.setopt(c.CONNECTTIMEOUT, 30)
		c.setopt(c.TIMEOUT, 120)
		c.setopt(c.POST, 1)
		c.setopt(c.USERAGENT, "ApiSCSE;" + str(self.VERSION))
		c.setopt(c.HTTPAUTH, pycurl.HTTPAUTH_BASIC)

		if arguments:
			api_object = json.dumps(arguments)
			a = {'json' : api_object}
			d = urlencode(a)
			c.setopt(c.POST, True)
			c.setopt(c.POSTFIELDS, d)

		url = 'http://scraping.services/api?user=' + str(self.api_login) + "&credentials=" + str(self.api_key) + "&V=" + str(self.VERSION) + "&type=json&job_type=" + job_type + "&task=" + task
		c.setopt(c.URL, url)
		c.setopt(c.WRITEDATA, buffer)

		c.perform()

		data = buffer.getvalue().decode('utf-8')
		if not data:
			self.error_msg = 'Network connection error'
			return False

		c.close()
		ret = json.loads(data)

		if ret:
			response = ApiResponse()
			response.answer = ret['answer']

			for exception in ret['exceptions']:
				response.exceptions.append(exception)
				response.has_exception = True

			self.last_response = response
			return response

		self.error_msg = 'Invalid or no information received'

		return False