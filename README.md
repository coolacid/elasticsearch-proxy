elasticsearch-proxy
===================

A simple PHP based Elasticsearch Proxy to add Filters on the fly based on user groups.

Long Version
------------

What I needed to be able to do is add filters on the fly based on a user. Initially I'll take the user from the Apache Process, and apply directly to per user.

Why?
----

I've had the question; But why? 

Well, the concept of least privilege applies here. You should only be giving access to data any given user actually NEEDS to complete their role. 

Let's try some examples, given you have Domain Controller, Firewall, VPN, and Web logs.
- Does your Active directory people actually need access to firewall, VPN and web logs?
- Does your web master actually need access to Domain Controller, VPN logs?

It has a second bonus, it makes it easier for people to get the data they need, without having to see the clutter from other logs.

Why *This* Method
------------------

I've had a lot of questions as to why use this instead of [Filtered Aliases] http://www.elasticsearch.org/blog/restricting-users-kibana-filtered-aliases/

Well, filtered alias is OK, but takes quite a bit of work to get setup the first time, IE: Defining your requirements, setting up the users, creating the aliases. But..
- What happens if those requirements change? Someone changes roles, new person comes into the org? You'd have to re-index all your data.  
- Also, Logstash uses dynamic indexes, thus, (until dynamic aliases is available), you'd have to cron creating the new aliases. 

Another option that was proposed was using targeted Kibana consoles, yeah, this would give a view for a particular role, but..
- You can't lock down the access, all the "checks" are client side, thus, any user can still access all the data
- It doesn't stop someone from sending a DELETE or PUT command, unless you proxy those.

Another implementation, acts as a webserver and proxy in Ruby: https://github.com/christian-marie/kibana3_auth

Small Issue
-----------

After talking with a Lucene developer, it was identified that this will not work 100%. Although, it does work, if a user specially crafts a search query, they could in fact get access to data they were not intended on having.

The way elasticsearch/lucene handles queries, it applies a weight to the results. When we add our filters, it changes those weights as well. By no means will elasticsearch actually remove items, or not make them available.

Should a user be able to identify the filter being applied, in theory, they could negate it by adding the opposite filters. 


Why PHP
-------

Well, to start, it's a programming language I know well enough to release as. I've been working with PHP for years and understand that PHP is only as good as your code (like all the others). Yes, I know I still need to harden the code :)

Also, since for Kibana we already have a web server, there really isn't a need for yet ANOTHER port to be open just to proxy requests. This serves the purpose.

I may spend some time re-doing this in Python - but, I'd have to learn how to CGI python, plus, PHP and web servers play well together already. 


To-do Steps
----------

- Add Authentication "plugins" so we don't have to rely on web process to authenticate the user


TipJar: https://gist.github.com/coolacid/9537573
