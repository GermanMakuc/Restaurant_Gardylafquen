export const es = {
  app: {
    brand: 'Restaurant Gardylafquen',
    tagline: 'Panel de gestión',
    skipToContent: 'Saltar al contenido principal',
    footerNote: 'Restaurant Gardylafquen — Arquitectura API REST + Astro',
  },
  nav: {
    ariaLabel: 'Navegación principal',
    home: 'Inicio',
    orders: 'Órdenes',
    sales: 'Ventas',
    waiters: 'Meseros',
    products: 'Productos',
    categories: 'Categorías',
  },
  home: {
    pageTitle: 'Inicio | Restaurant Gardylafquen',
    heading: 'Panel principal',
    subtitle:
      'Base de la nueva arquitectura desacoplada sobre API REST Laravel y frontend Astro con Tailwind CSS.',
    apiSectionTitle: 'Estado del servicio API',
    modulesSectionTitle: 'Módulos planificados (Fase 2)',
    pendingBadge: 'Pendiente de migración',
    modules: [
      {
        name: 'Categorías',
        description: 'Clasificación de productos del menú.',
      },
      {
        name: 'Productos',
        description: 'Catálogo de platos, bebidas, precios e imágenes.',
      },
      {
        name: 'Meseros y mesas',
        description: 'Asignación de personal de salón y estado de mesas.',
      },
      {
        name: 'Órdenes',
        description: 'Toma de pedidos en sala y seguimiento por mesa.',
      },
      {
        name: 'Ventas y tickets',
        description: 'Cierre de cuenta, emisión de ticket y control de caja.',
      },
    ],
  },
  health: {
    apiLabel: 'API REST',
    databaseLabel: 'Base de datos',
    timestampLabel: 'Última comprobación',
    statusOk: 'Operativa',
    statusDegraded: 'Degradada',
    statusUnreachable: 'Sin conexión',
    dbOk: 'Conectada',
    dbError: 'Sin conexión',
  },
} as const;
