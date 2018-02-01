To contribute to bread, fork, make your changes and send a pull request to the unstable branch.

Take a look at the issues for bugs that you might be able to help fix.

Once your pull request is merged it will be released in the next version.

If you are the maintainer of this code you will have to squash the commits to master from unstable as follows.

```shell
git checkout master
git rebase origin/master
git merge --squash unstable
git commit -m "version x.x.x"
git push -u origin master
git svn dcommit --username=radius314

git checkout unstable
git rebase master
git push -f origin unstable
```

To get things going in your local environment.

`docker-compose up`

Get your wordpress installation going.  Remember your admin password.  Once it's up, login to admin and activate the BMLT meeting list plugin.

Now you can make edits to the bmlt-meeting-list.php file and it will instantly take effect.