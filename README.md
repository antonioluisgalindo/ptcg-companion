# PTCG Companion 🏆

**PTCG Companion** es una plataforma profesional diseñada para optimizar la organización y gestión de torneos del **Juego de Cartas Coleccionables Pokémon (TCG)**. Desde la inscripción de jugadores hasta la exportación de resultados, centraliza todas las herramientas necesarias para organizadores y jugadores.

## ✨ Características Principales

### 📋 Gestión de Torneos
- **Ciclo de Vida Completo:** Control total sobre los estados del torneo (Borrador, Inscripciones, En Curso, Finalizado, Cancelado).
- **Formatos Oficiales:** Soporte para formatos Standard, Expanded y Unlimited.
- **Formatos de Partidas:** Soporte para encuentros al Mejor de 1 (Bo1) y Mejor de 3 (Bo3).
- **Top Cut:** Configuración de eliminatorias (Top 4, 8, 16, 32) tras las rondas suizas (sin empates permitidos en rondas de corte).
- **Sistema de Notificaciones:** Avisos automatizados a los jugadores sobre el estado de sus inscripciones, inicio de rondas o confirmación de resultados.

### 📍 Localización Inteligente
- **Geoposicionamiento:** Clasificación de torneos por **Provincia** y **Localidad**.
- **Filtros Avanzados:** Buscador con selectores enlazados (la provincia filtra dinámicamente las localidades mediante AJAX).

### 👥 Gestión de Participantes (UX Premium)
- **Modal de Inscritos Asíncrono:** Administración de jugadores sin recargar la página.
- **Estadísticas en Tiempo Real:** Conteo automático de jugadores por categorías de edad (Master, Senior, Junior).
- **Buscador Integrado:** Filtrado instantáneo de inscritos por nombre o Player ID.

### 📱 Inscripción Ágil
- **Códigos QR y Códigos de Acceso:** Los organizadores pueden mostrar un código QR dinámico o proveer un código alfanumérico para que los jugadores se unan al instante.
- **Validación Automática:** Comprobación obligatoria de Categoría (fecha de nacimiento) y Player ID oficial para inscripciones válidas.
- **Control de Barajas:** Los torneos pueden configurarse para exigir el envío obligatorio de la lista del mazo (decklist) al registrarse.
- **Autogestión de Inscripciones:** Los jugadores tienen la opción de cancelar su inscripción de forma autónoma durante la fase de registro.

### ⚙️ Integración con TOM
- **Importación Flexible:** Permite cargar bases de datos de jugadores y emparejamientos de rondas directamente desde el software **TOM (Tournament Official Manager)** mediante copiado/pegado de tablas o archivos CSV.

### ⚔️ Sistema de Emparejamientos
- **Swiss System y Standings:** Emparejamientos automáticos y cálculo de clasificaciones tras cada ronda basándose en el rendimiento.
- **Gestión de Resultados:** Los propios jugadores pueden reportar los marcadores de sus partidas, contando con reglas estrictas de validación (p.ej., control exacto de resultados en formato Bo1).
- **Intervención Arbitral:** Los usuarios con rol de Juez o Administrador tienen privilegios para editar resultados, confirmar partidas y resolver posibles conflictos.

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

## 📖 Flujos de Trabajo por Roles

### 🃏 Jugadores
1. **Registro y Perfil:** Regístrate y completa tu perfil con tu fecha de nacimiento (necesaria para determinar la categoría Master, Senior o Junior) y tu Player ID.
2. **Inscripción:** Busca torneos públicos en tu zona o utiliza el código de acceso (alfanumérico o QR) proporcionado por el organizador.
3. **Decklist:** Durante la inscripción, proporciona la lista de tu mazo si el torneo lo exige.
4. **Autogestión:** Puedes cancelar tu inscripción de manera autónoma si el torneo aún se encuentra en fase de registro.
5. **Desarrollo del Torneo:** Una vez iniciado, consulta tus emparejamientos ronda a ronda y reporta el resultado de tus partidas directamente desde tu dispositivo.

### 📝 Organizadores
1. **Creación del Torneo:** Crea un torneo definiendo el formato, tipo de partida (Bo1/Bo3), cupo de jugadores y requisitos como la entrega obligatoria de decklist.
2. **Gestión de Inscritos:** Utiliza el panel asíncrono para aceptar o dar de baja jugadores que solicitan inscripción. Comparte el código de acceso o el QR para agilizar el proceso en el local.
3. **Ejecución:** Cambia el estado del torneo a "En Curso" e inicia las rondas. El sistema generará emparejamientos automáticamente.
4. **Top Cut y Finalización:** Configura las eliminatorias según la clasificación final y, tras concluir el evento, marca el torneo como finalizado.

### ⚖️ Jueces
1. **Supervisión de Rondas:** Accede a los torneos para monitorizar el estado de las rondas activas.
2. **Resolución de Conflictos:** Si los jugadores introducen resultados erróneos o hay disputas, utiliza la intervención arbitral para editar y forzar el resultado correcto de una partida.
3. **Auditoría:** Verifica que las normas se cumplen en rondas específicas (ej. impidiendo empates en Top Cut) y aplica notas u observaciones a las partidas.

### 🛡️ Administradores
1. **Control Total:** Acceso sin restricciones a todos los torneos, incluso aquellos en estado de "Borrador" que no sean de su autoría.
2. **Gestión de Usuarios:** Asignación y revocación de roles (Organizador, Juez) al resto de cuentas.
3. **Mantenimiento:** Supervisión de logs de actividad, ajustes globales de la plataforma y mantenimiento del sistema.

---

Desarrollado con ❤️ para la comunidad de Pokémon TCG.
