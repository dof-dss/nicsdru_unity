# Microsite template project

This project is a common ancestor for all microsites. It is not intended to be run itself (but can be), rather forked into a new repository when a microsite is needed.

There is no composer.lock file here, because we want composer to generate that when you fork + let composer work out the dependency tree at point you create your new project.

Look in composer.json to see the key dependencies. It goes without saying your profile will probably depend on contrib and other third party code, maybe keep its own custom code too. Let composer work that out for you (it reads the composer.json files for each project) and build the dependency tree or grumble at you when it can't satisfy all dependencies defined (it'll tell you why not).
