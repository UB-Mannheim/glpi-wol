# GLPI WOL
Simple plugin that adds basic Wake On LAN (WOL) functionality.

WOL signals can be send via "Actions" for a single or multiple targets.

## Installation
Use `git clone https://github.com/UB-Mannheim/glpi-wol.git /path/to/glpi/plugins/wakeonlan`, then install and configure via webfrontend (Setup > Plugins).

## Configuration & Usage
You may configure the plugin to either
- send a WOL signal from the same server as GLPI ("local") or
- send MAC and broadcast address via POST request to a URL ("remote").

### Send WOL from GLPI server
No further plugin configuration is required, but make sure that the server is allowed to send WOL signals in your network.

### Posting to remote URL
This is useful e.g. if your GLPI server is not allowed to send WOL signals in the target network, but another server is.

You must provide a URL to which MAC and broadcast addresses are sent (and also the logic under that URL).

You can specify names for the POST fields that contain MAC and broadcast addresses; if unconfigured 'mac' and 'broad' are assumed.

You may optionally specify a string of that is appended to the POST fields as is. Example: `&name1=value1&name2=value2`.

As a response the plugin expects a JSON dictionary in the form of:

```
{
  "success": true/false,
  "msg": "Arbitrary message, in practice only relevant for informing about errors."
}
```

## Known limitations
- Not localized yet.
- Not tested with IPv6 yet.
- Does not confirm if the WOL signal reached the target.
