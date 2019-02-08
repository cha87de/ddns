# ddns
Dynamic DNS Service (PHP + Bind)

## Usage

```
docker build -t ddns .
docker run --rm -ti \
    -p 8080:80 \
    -p 553:53 -p 553:53/udp \
    -e DDNS_DOMAIN="testdomain.local" \
    -e DDNS_IPv4="192.168.2.11" \
    -v $(pwd)/example-data:/opt/data/ \
    --name ddns \
    ddns
```
