# HomeLib📖

Web App para guardado de bibliotecas personales.

## Instalación y deployment

Descarga o clona el proyecto, para deployearlo necesitarás *node.js* y *npm*, luego:

```bash
npm run build
```
Y lo que genere en la carpeta **/dist** es lo que tendrás que pasar tu servicio de hosting.
Para conectar una base de datos tendrás que crear un archivo con nombre *db.json* en la carpeta **/config** del proyecto con esta sintaxis:
```json
{
"DB_NAME": "",
"DB_USER": "",
"DB_PASS": "",
"DB_HOST": "",
"DB_PORT": ""
}
```
