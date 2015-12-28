### Podbe Node (das firtz-knoten)

* powered by firtz
* in Arbeit!



##### Screenshot

<img src="https://raw.githubusercontent.com/Podbe/Podbe_V4-firtz-node/master/screencapture-admin.png">


// --------------------------- rechte vergabe -----------------------

#### installation:

Die installation ist einfach. Lade dir den Node herunter. Du benötigst eine Subdomain oder direkte Domain zum 
Ausführen des Node.

<code>
Beispiel: &lt;domain.tld&gt; oder &lt;node.domain.tld&gt;
</code>

#### setup:

* go to <code>/app/controller/setup.php</code>
 * edit username & password - this are the login data from podbe api call (not user data!)
 * fill in the category (find more in the podbe api documentation)

* create your personal admin login in the setup.php
 * you can login under: <code>&lt;domain.tld&gt;/home/admin/login</code>

#### rights on server:

* create: <code>/tmp</code> (www-data: 777)
* rights: <code>/app/assets/db/</code> (www-data: 777)

// ----------------------------- change log -------------------------

*v 0.0.2* system daten speichern in der node.db

- changes: speichern des tokens nach dem login in der node.db
- changes: node.db speichert datum + token (datum wird später noch verwendung finden)
- changes: routing.php, admin.php, sqlite.php, index.php
 

*v 0.0.1* start firtz-knoten

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

### license 

// ------------------------ firtz-node 2015/2016 ---------------------

The MIT License (MIT)

Copyright (c) 2015-2016 Michael McCouman Jr. ( Redesign to directory: firtz-node ),

Copyright (c) 2013-2015 Christian Bednarek ( firtz 2.0 )

// ----------------------------- firtz 2015 -------------------------

The MIT License (MIT)

Copyright (c) 2013-2015 Christian Bednarek ( firtz 2.0 )

Copyright (c) 2015 Michael McCouman Jr. ( quorx II design )

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.