# 🤡 Unattended Upgrades

Updates apps automatically

![Downloads](https://img.shields.io/github/downloads/ChristophWurst/unattended_upgrades/total.svg)

The app is still under development, so it’s time for you to get involved! 👩‍💻

## How it works

The app will register a background job that runs once an hour. Depending on the configuration, it will update all, some or none of the upgradable apps.

## Configuration

The app configuration is specified via `config/config.php`.

```
  'unattended_upgrades' => [
      // your preferences
  ],
```

### Maintenance window

You can set a time window between which the upgrade is allowed.

```
  'unattended_upgrades' => [
      'maintenance_window' => [
          'start' => '06:00',
          'end' => '08:00',
      ],
  ],
```

### Blocked apps

If you know that an app has questionable QA policies you may want to exclude it from the automated upgrade to save yourself from random fire fighting.

```
  'unattended_upgrades' => [
      'blocked' => [
          'unattended_upgrades',
      ],
  ],
```

### Allowed apps

If you're feeling like the automated upgrades of every app are too much risk for you, you may pick the cherries and only allow certain apps.

```
  'unattended_upgrades' => [
      'allowed' => [
          'mail',
      ],
  ],
```

## FAQ

Commonly asked questions.

### Should I run this in production?

No, this app is an experiment. However, if you like living on the edge you may of course deploy the app anyway.
