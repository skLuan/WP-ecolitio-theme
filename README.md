# Ecolitio WordPress Docker Setup

## Performance Optimizations Applied

### Docker Configuration
- Added resource limits to containers:
  - WordPress: 2 CPUs, 2GB RAM (reserved 1 CPU, 1GB)
  - MariaDB: 1 CPU, 1GB RAM (reserved 0.5 CPU, 512MB)

### Database Tuning
- MariaDB configured with:
  - innodb-buffer-pool-size: 256M
  - innodb-log-file-size: 64M
  - max-connections: 100

### Development Notes
- Debug mode kept enabled for development
- LiteSpeed Cache plugin available but disabled (activation failed due to permissions)
- Heavy plugins (Elementor, WooCommerce) kept active as they may be required

### Monitoring Performance
To check performance improvements:
- Use browser dev tools to measure page load times
- Monitor Docker stats: `docker stats`
- Check MariaDB slow queries if needed

### Commands
- Start: `npm run start`
- Stop: `npm run stop`
- Restart: `npm run restart`
- Logs: `npm run logs`
- WP-CLI: `npm run wp <command>`
- Shell: `npm run shell`