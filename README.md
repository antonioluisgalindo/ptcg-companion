# PTCG Companion 🏆

**PTCG Companion** es una plataforma profesional diseñada para optimizar la organización y gestión de torneos del **Juego de Cartas Coleccionables Pokémon (TCG)**. Desde la inscripción de jugadores hasta la exportación de resultados, centraliza todas las herramientas necesarias para organizadores y jugadores.

## ✨ Características Principales

### 📋 Gestión de Torneos
- **Ciclo de Vida Completo:** Control total sobre los estados del torneo (Borrador, Inscripciones, En Curso, Finalizado, Cancelado).
- **Formatos Oficiales:** Soporte para formatos Standard, Expanded y Unlimited.
- **Top Cut:** Configuración de eliminatorias (Top 4, 8, 16, 32) tras las rondas suizas.

### 📍 Localización Inteligente
- **Geoposicionamiento:** Clasificación de torneos por **Provincia** y **Localidad**.
- **Filtros Avanzados:** Buscador con selectores enlazados (la provincia filtra dinámicamente las localidades mediante AJAX).

### 👥 Gestión de Participantes (UX Premium)
- **Modal de Inscritos Asíncrono:** Adminitración de jugadores sin recargar la página.
- **Estadísticas en Tiempo Real:** Conteo automático de jugadores por categorías de edad (Master, Senior, Junior).
- **Buscador Integrado:** Filtrado instantáneo de inscritos por nombre o Player ID.

### 📱 Inscripción Ágil
- **Códigos QR:** Los organizadores pueden mostrar un código QR dinámico para que los jugadores se unan al instante.
- **Validación Automática:** Comprobación obligatoria de Categoría (fecha de nacimiento) y Player ID oficial para inscripciones válidas.

### ⚙️ Integración con TOM
- **Importación Flexible:** Permite cargar bases de datos de jugadores y emparejamientos de rondas directamente desde el software **TOM (Tournament Official Manager)** mediante copiado/pegado de tablas o archivos CSV.

### ⚔️ Sistema de Emparejamientos
- **Swiss System:** Emparejamientos automáticos basados en el rendimiento de los jugadores.
- **Gestión de Resultados:** Interfaz intuitiva para que los jugadores o jueces reporten los marcadores de las partidas.

---

## 🛠️ Stack Tecnológico

- **Backend:** Laravel 11 (PHP 8.2+)
- **Frontend:** HTML5, CSS3 (Diseño Premium con variables personalizadas), JavaScript (Vanilla AJAX / Fetch API).
- **Base de Datos:** MySQL / MariaDB.
- **Seguridad:** Gestión de roles y permisos (Admin, Organizador, Juez, Jugador) mediante Spatie Laravel-Permission.

---

## 🚀 Instalación y Configuración

1. **Clonar el repositorio:**
   ```bash
   git clone git@gitlab.com:antoniogalindo/ptcg-companion.git
   ```

2. **Instalar dependencias:**
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Configurar el entorno:**
   - Copia `.env.example` a `.env`.
   - Genera la clave de aplicación: `php artisan key:generate`.
   - Configura tus credenciales de base de datos en el `.env`.

4. **Migraciones y Datos Base:**
   ```bash
   php artisan migrate --seed
   php artisan db:seed --class=LocationSeeder
   ```

---

## 📖 Cómo funciona

1. **Para Jugadores:** Completa tu perfil con tu fecha de nacimiento (para asignar tu categoría MA, SR o JR) y tu Player ID. Una vez hecho, puedes inscribirte en cualquier torneo público o mediante el código QR del organizador.
2. **Para Organizadores:** Crea un torneo, configura el formato y las rondas. Utiliza el modal de inscripciones para confirmar o dar de baja a los jugadores. Cuando estés listo, abre las rondas y gestiona los emparejamientos.
3. **Para Administradores:** Gestiona los roles de los usuarios, supervisa los logs de actividad y configura los parámetros globales de la aplicación.

---

Desarrollado con ❤️ para la comunidad de Pokémon TCG.
