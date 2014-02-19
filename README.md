elasticsearch-proxy
===================

A simple PHP based ElasticSearch Proxy to add Filters on the fly based on user groups.

Long Version
------------

What I needed to be able todo is add filters on the fly based on a user. Initiially I'll take the user from the Apache Process, and apply directly to per user.

Todo Steps
----------

- Get it working by adding filter on the fly
- Detect user, add filter based on user id
- Add Groups, so you can assign users to groups, then add filters based on those groups
- Config file - so you don't have to hard code things in PHP, just write a "config"
- Add Authentication "plugins" so we don't have to rely on web process to authenticate the user



