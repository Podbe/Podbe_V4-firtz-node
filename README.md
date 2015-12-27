### Podbe Node (das firtz-knoten)

* in Arbeit!

// --------------------------- rechte vergabe -----------------------

### Setup:
* go to /app/controller/setup.php
 * edit username & password - this are the login data from podbe api call (not user data!)
 * fill in the category (find more in the podbe api documentation)

* create your personal admin login in the setup.php
 * you can login under: &lt;domain.tld&gt;/home/admin/login

### rights on server:

* create: /tmp (www-data: 777)
* rights: /app/assets/db/ (www-data: 777)

// ----------------------------- change log -------------------------

*v 0.0.1*

- changes: new file and folders for new data structure 
- changes: introduction: app 
- changes: introduction: controller
- changes: introduction: routing
- changes: introduction: modals
- changes: introduction: settings
- changes: introduction in app: config.ini
- changes: user folder for all podcast owner
- new: admin area: oAuth to connect to podbe api
- new: admin theme: adminpages (home, settings, update)
- new: admin page read_json: load json from podbe api 
- new: admin page update: delete nodes
- new: admin page: no cache
- new: admin db: sqlite db to create, read, delete all new podcast
- new: node: new meta and data types in .htaccess 