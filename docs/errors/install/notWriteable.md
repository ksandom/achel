# Not writeable

One of more destination directories is not writeable. Most likely you have chosen a linked or user install.

The easy way out is to simply run the install as root and a root install will be chosen, which will be more convenient, especially if you want to share the installation with other users.

However, if you'd like to understand and fix the problem the error will be immediately before this message. You can see what paths the install wants to use by doing ./install.sh --showConfig
