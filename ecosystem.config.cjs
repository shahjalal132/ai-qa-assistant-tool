module.exports = {
  apps: [{
    name: 'laravel-horizon',
    script: 'artisan',
    interpreter: 'php',
    args: 'horizon',
    instances: 1,      // Always 1! Horizon handles its own children.
    autorestart: true,
    watch: false,
    max_memory_restart: '512M',
  }]
};