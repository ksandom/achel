# Working with config items
## Defining config items (inside a module)

    registerConfigItem($settingName, $subcategory, $description, $type='array')

 * `$settingName`
 * `$subcategory`
 * `$description`
 * `$type`
  ** array
  ** TODO check what the others are. eg string?

Here are some lines in __construct() which create some config items and set their defaults in the RegexFaucet:

    $this->registerConfigItem('rules', '', 'Regex rules to define where data should be sent. --addFaucetConfigItemEntry=faucetName,rules,,ruleName,,matchRegex,destinationRegex', 'array');
    $this->registerConfigItem('onlyFirst', '', 'Send only to the first match. The alternative is to send to every match. Expecting 0 or 1.', 'integer');
    $this->setConfigItem('onlyFirst', '', '1');
    
    $this->registerConfigItem('defaultOut', '', 'If no channels match, this is the channel where the data will be sent.', 'string');
    $this->setConfigItem('defaultOut', '', 'default');
