To contribute to bread, fork, make your changes and send a pull request to the main branch.

Take a look at the issues for bugs that you might be able to help fix.

Once your pull request is merged it will be released in the next version.

To get things going in your local environment, run the following:

```shell
composer install
docker-compose up
```

Get your wordpress installation going.  Remember your admin password.  Once it's up, login to admin and activate the bread plugin.

Now you can make edits to the bmlt-meeting-list.php file and it will instantly take effect.

To profile a PHP page, include `XDEBUG_PROFILE` as part of the querystring.  The result will be in `/tmp/cachegrind.out.???`, the extension being the process ID.  

You can then open this file in IntelliJ, Valgrind, or any other tool that let's you review an Xdebug profiler result.

If you are using a mac for development and Homebrew, this is a fairly easy way to get going.

`brew install qcachegrind --with-graphviz`
