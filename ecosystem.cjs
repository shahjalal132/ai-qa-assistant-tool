module.exports = {
    apps: [{
      name: 'sber-ai-worker',
      script: 'php',
      args: 'artisan queue:work --tries=3 --sleep=3 --timeout=60',
      instances: 5,
      exec_mode: 'fork',
      autorestart: true,
      watch: false,
      max_memory_restart: '512M',
      env: {
        NODE_ENV: 'production',
      },
      time: true,
    }]
};