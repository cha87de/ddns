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

Where DDNS_DOMAIN is the domain name for the DNS server and DDNS_IPv4 is the IP
address for this DNS server. Next, define the dynamic subomains. Update the
`domains.json` file accordingly, e.g. token1 for home.testdomain.local and
token2 for office.testdomain.local:

```
[{
    "home": "token1",
    "office": "token2"
}]
```