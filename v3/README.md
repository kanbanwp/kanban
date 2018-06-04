# Kanban For WordPress 3.0

## Installation

* `cd /board`
* `npm install`
* `node_modules/.bin/webpack -w

Javascript is compiled to /boaard/js/app.js.
Css is compiled to /boaard/css/app.css.

## API

Requests are made to the back end by posting to this url: `WORDPRESS_URL/kanban/ajax`, e.g. `http://kanban.local:8888/kanban/ajax` if developing locally. Here is a sample request:

Set `Content-Type` header to `application/x-www-form-urlencoded`, then use a set of key-values:

| key  |  value |
|---|---|
|  content |  Tom G |
|  card_id | 13  |
|  field_id |  2 |
|  id |  3 |
|  type |  fieldvalue |
|  action |  replace |

The response from the back end:

```
{
  "success": true,
  "data": {
    "id": "3",
    "is_active": true,
    "created_dt_gmt": "2017-12-18 20:11:58",
    "created_user_id": "1",
    "modified_dt_gmt": "2018-01-04 19:43:43",
    "modified_user_id": "0",
    "content": "tmoney griffdog",
    "card_id": "13",
    "field_id": "2",
    "isNew": false
  }
}
```

Responses are sent via `/ajax/index.php` and routed to the appropriate class in `src/Kanban/`. 
In the above example, the method `replace` is called in `/src/Kanban/Fieldvalue.php` because of the key-value pairs `type: fieldvalue` and `action: replace` in the request. In this way, each JS module in `/app/src/model/` corresponds to a PHP class in `/src/Kanban/`.
