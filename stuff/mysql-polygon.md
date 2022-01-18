# Mysql polygon area

### Polygon, point with geo_json
```json
{"type":"Polygon","coordinates":[[[20.834158077340533,52.11296920104141],[21.034658565621783,52.385376972929855],[21.397207393746783,52.15848668447624],[20.834158077340533,52.11296920104141]]]}

{"type": "Point", "coordinates": [11.11, 12.22]}
```

### Point
```sql
1. SELECT ST_AsGeoJSON(ST_GeomFromText('POINT(11.11111 12.22222)'),6);

2. SET @json = '{"type": "Point", "coordinates": [11.11, 12.22]}';

3. SELECT ST_GeomFromGeoJSON(@json);
```

### Distance
```sql
CREATE TABLE cities(name VARCHAR(200), geo GEOMETRY(4326));

INSERT INTO cities VALUES('Berlin', PointFromText('POINT(13.36963 52.52493)'));
INSERT INTO cities VALUES('London', PointFromText('POINT(-0.1233 51.5309)'));

-- this shows the distance in degrees:
SELECT a.name, b.name, st_distance(a.geo, b.geo) FROM cities a, cities b;
-- this shows the distance in meters:
SELECT a.name, b.name, ST_DISTANCE_SPHERE(a.geo, b.geo) FROM cities a, cities b;
-- this shows the distance in km:
SELECT a.name, b.name, ST_DISTANCE_SPHERE(a.geo, b.geo) * .001 FROM cities a, cities b;
```

# Aws redshift 
```sql
1. SELECT ST_Contains(ST_GeomFromText('POLYGON((0 2,1 1,0 -1,0 2))'), ST_GeomFromText('POLYGON((-1 3,2 1,0 -3,-1 3))'));
2. SELECT ST_Distance(ST_GeogFromText('point(1 1)'),ST_GeogFromText('point( -21.32 121.2)';
```
