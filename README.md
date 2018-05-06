# TS3 Ranksystem

System that automatically grants ranks in the form of servergroups for online time or online activity to users, using the given server groups of the TS3 server.

## Differences
Differences to [Newcomer1989/TSN-Ranksystem](https://github.com/Newcomer1989/TSN-Ranksystem)

- **No** shady base64 encoded "DRM" reporting to *ts-n.net* (see [21e261c](https://github.com/JVMerkle/TS3-Ranksystem/commit/21e261cf949278b872b7cfc4e54e738b74962322)) such as
    - Copyright notice check for [info.php](stats/info.php)
    - Sidebar link check for [info.php](stats/info.php)
- No self-updating process (see [21e261c](https://github.com/JVMerkle/TS3-Ranksystem/commit/1e282f1ae9098e2cb201f47f0812ba4fb19c8f51)), which exposes information such as
    - TeamSpeak Statistics such as users per day/week/month/quarter 
    - Teamspeak hostname and port (e.g. localhost / abc.xyz)
    - ...
- No April fool
- [CSRF](https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)) Protection

## License
This project is licensed under the terms of the GNU General Public License v3.0.