## firtz-node changelog:

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
