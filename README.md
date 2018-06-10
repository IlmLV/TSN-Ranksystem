# TS3 Ranksystem

System that automatically grants ranks in the form of servergroups for online time or online activity to users, using the given server groups of the TS3 server.

## Differences
Differences to [Newcomer1989/TSN-Ranksystem](https://github.com/Newcomer1989/TSN-Ranksystem)

- Rank management changes:
    - allows to keep manually set higher ranks - this is high value option for server admins who previosly didn't used TSN-Ranksystem and was manually setting user ranks OR if online time is not the only way to earn higher ranks
    - allows to use forced single rank group usage - if any of cases user currently has more than one ranksystem group, all, except one higher ranked, will be removed
    - Both rank management options is defined in _Core settings_ section, where thoes can be enabled, by default they are disabled - works by previous behavior.
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
