# ARQUITECTURA MVC/POO - GOLNET
## Proyecto: FIFA World Cup 2026 Social Platform

**Estado: 100% MVC/POO Compliant ✅**
**Fecha de finalización: 2024**

---

## 📁 ESTRUCTURA DEL PROYECTO

```
CapaIntermedia-main/
├── app/
│   ├── Core/
│   │   └── Database.php                 # Singleton para conexión a base de datos
│   │
│   ├── Repositories/                    # Capa de acceso a datos
│   │   ├── AuthRepository.php           # Autenticación (login, registro, logout)
│   │   ├── CatalogRepository.php        # Categorías y mundiales
│   │   ├── PublicationRepository.php    # Publicaciones, comentarios, likes
│   │   └── UserRepository.php           # Usuarios, perfiles, verificaciones
│   │
│   ├── Controllers/                     # Lógica de negocio
│   │   ├── AdminController.php          # Panel de administración
│   │   ├── BuscarController.php         # Búsquedas
│   │   ├── CategoriaController.php      # Detalle de categoría
│   │   ├── CategoriasController.php     # Listado de categorías
│   │   ├── ComentariosController.php    # Comentarios de publicación
│   │   ├── CrearPublicacionController.php  # Crear nueva publicación
│   │   ├── EditarPerfilController.php   # Editar perfil de usuario
│   │   ├── EditarPublicacionController.php # Editar publicación existente
│   │   ├── InicioController.php         # Feed de publicaciones
│   │   ├── LoginController.php          # Iniciar sesión
│   │   ├── LogoutController.php         # Cerrar sesión
│   │   ├── MisPublicacionesController.php # Perfil de usuario
│   │   ├── MundialDetalleController.php # Detalle de mundial
│   │   ├── MundialesController.php      # Listado de mundiales
│   │   ├── RegistroController.php       # Registro de usuario
│   │   │
│   │   └── API/                         # Controladores para AJAX endpoints
│   │       ├── CommentActionApiController.php     # Toggle like en comentario
│   │       ├── CommentApiController.php           # Agregar comentario
│   │       ├── CommentStatusApiController.php     # Aprobar/rechazar comentario (admin)
│   │       ├── DeleteCommentApiController.php     # Eliminar comentario
│   │       ├── GetCommentersApiController.php     # Obtener lista de comentaristas
│   │       ├── GetLikersApiController.php         # Obtener lista de usuarios que dieron like
│   │       ├── LikeApiController.php              # Toggle like en publicación
│   │       └── PublicationActionApiController.php # Aprobar/rechazar publicación (admin)
│   │
│   └── Views/                           # Capa de presentación
│       ├── administrar_publicaciones.php
│       ├── buscar.php
│       ├── categoria.php
│       ├── categorias.php
│       ├── comentarios_publicacion.php
│       ├── crear_publicacion.php
│       ├── detalle_mundial.php
│       ├── editar_perfil.php
│       ├── editar_publicacion.php
│       ├── inicio.php
│       ├── iniciar_sesion.php
│       ├── mis_publicaciones.php
│       ├── mundiales.php
│       └── registro.php
│
├── public/                              # Bootstrap files (entry points) - WEBROOT
│   ├── administrar_publicaciones.php    # Admin panel bootstrap
│   ├── buscar.php                       # Search bootstrap
│   ├── categoria.php                    # Category detail bootstrap
│   ├── categorias.php                   # Categories list bootstrap
│   ├── cerrar_sesion.php                # Logout bootstrap
│   ├── comentarios_publicacion.php      # Comments bootstrap
│   ├── comment_action_handler.php       # Comment like API bootstrap
│   ├── comment_handler.php              # Add comment API bootstrap
│   ├── crear_publicacion.php            # Create publication bootstrap
│   ├── delete_comment_handler.php       # Delete comment API bootstrap
│   ├── detalle_mundial.php              # World Cup detail bootstrap
│   ├── editar_perfil.php                # Edit profile bootstrap
│   ├── editar_publicacion.php           # Edit publication bootstrap
│   ├── get_commenters_handler.php       # Get commenters API bootstrap
│   ├── get_likers_handler.php           # Get likers API bootstrap
│   ├── iniciar_sesion.php               # Login bootstrap
│   ├── inicio.php                       # Home/feed bootstrap
│   ├── like_handler.php                 # Like API bootstrap
│   ├── mis_publicaciones.php            # User profile bootstrap
│   ├── mundiales.php                    # World Cups list bootstrap
│   ├── publication_action_handler.php   # Publication admin API bootstrap
│   └── registro.php                     # Register bootstrap
│
├── css/                                 # Stylesheets
│   ├── admin.css
│   ├── detalle_mundial.css
│   ├── editar.css
│   ├── inicio.css
│   ├── login.css
│   └── nueva_publicacion.css
│
├── javascript/                          # Client-side scripts
│   ├── admin.js
│   ├── crear_publicacion.js
│   ├── edit_perfil.js
│   ├── edit_perfil.js
│   ├── inicio.js
│   └── registro.js
│
└── uploads/
    └── profile_pics/                    # User uploaded images
```

---

## 🏗️ PATRONES DE DISEÑO IMPLEMENTADOS

### 1. **MVC (Model-View-Controller)**
- **Model (Repositories)**: Capa de acceso a datos que encapsula toda la lógica de base de datos
- **View (app/Views/)**: Archivos PHP con HTML/CSS/JS que renderizan la interfaz
- **Controller (app/Controllers/)**: Lógica de negocio que coordina Model y View

### 2. **Repository Pattern**
- Abstracción de toda la lógica de acceso a datos
- Cada repositorio se encarga de un área específica del modelo de datos
- Todos los stored procedures se ejecutan desde los repositorios

### 3. **Singleton Pattern**
- `Database::getConnection()` garantiza una única instancia de conexión MySQL
- Previene múltiples conexiones simultáneas

### 4. **Bootstrap Pattern**
- Archivos en `public/` son entry points mínimos (8-12 líneas)
- Cada bootstrap instancia su controlador y llama a `handle()`
- Esta carpeta es el único directorio accesible públicamente (webroot)

---

## 🔧 ARQUITECTURA TÉCNICA

### Stack Tecnológico
- **Backend**: PHP 7.4+ con POO
- **Base de datos**: MySQL con Stored Procedures (25+ SPs)
- **Frontend**: HTML5, CSS3, JavaScript vanilla
- **Librerías**: CKEditor 5, SweetAlert2, Font Awesome 6
- **Servidor**: XAMPP (Apache + PHP + MySQL)

### Principios POO Aplicados
✅ **Encapsulación**: Toda la lógica de negocio dentro de clases  
✅ **Separación de responsabilidades**: Repositories, Controllers, Views separados  
✅ **Type Hinting**: Parámetros y retornos tipados en métodos  
✅ **Visibilidad**: Uso correcto de `private`, `protected`, `public`  
✅ **Inyección de dependencias**: Controllers reciben repositories en constructor  
✅ **Single Responsibility**: Cada clase tiene una única responsabilidad  

### Manejo de BLOB
- Imágenes y videos se envían usando `send_long_data()` en chunks de 8KB
- Previene errores de memoria con archivos grandes
- Se usa en: fotos de perfil, multimedia de publicaciones, logos de mundiales

---

## 📊 ESTADÍSTICAS DEL PROYECTO

### Componentes MVC
- **14 Controladores de Página**: Para renderizado de vistas completas
- **9 Controladores API**: Para endpoints AJAX con respuestas JSON
- **4 Repositories**: 30+ métodos de acceso a datos
- **14 Views**: Templates HTML/PHP limpios sin lógica de negocio
- **23 Bootstrap Files**: Entry points en `public/`

### Stored Procedures Utilizados
```sql
-- Autenticación (AuthRepository)
SP_Login(Nombre_User, Password)
SP_NewUser(Nombre_User, Correo, Password, Fec_nac, TipoUsuario, Foto)
SP_VerifyUserPassword(ID_User, Password)

-- Usuarios (UserRepository)
SP_GetUserDetails(ID_User)
SP_UpdateUserProfile(ID_User, Nombre_User, Correo, Fec_nac, Foto)
SP_GetUserPublications(ID_User)

-- Publicaciones (PublicationRepository)
SP_NewPublication(Titulo, Descripcion, ID_User, ID_Cat, ID_Mundial, Multimedia, TipoMultimedia)
SP_GetAllApprovedPublications()
SP_GetPublicationById(ID_Publi)
SP_GetPublicationByIdForEdit(ID_Publi, ID_User)
SP_UpdatePublicationWithMultimedia(...) / SP_UpdatePublicationWithoutMultimedia(...)
SP_DeletePublication(ID_Publi, ID_User)
SP_ToggleLike(ID_User, ID_Publi)
SP_AddComment(ID_User, ID_Publi, Contenido)
SP_GetCommentsByPublication(ID_Publi)
SP_DeleteComment(ID_Coment, ID_User)
SP_GetPendingPublications()
SP_UpdatePublicationStatus(ID_Publi, Estado, MotivoRechazo)
SP_GetPendingComments()
SP_UpdateCommentStatus(ID_Coment, Estado)
SP_ToggleCommentLike(ID_User, ID_Coment)
SP_GetLikersByPost(ID_Publi)
SP_GetCommentersByPost(ID_Publi)

-- Catálogos (CatalogRepository)
SP_GetAllCategories()
SP_GetCategoryById(ID_Cat)
SP_GetPublicationsByCategory(ID_Cat)
SP_GetAllMundiales()
SP_GetMundialById(ID_Mundial)
SP_GetPublicationsByMundial(ID_Mundial)
SP_NewCategory(Nombre, Descripcion, Imagen)
SP_NewMundial(Nombre, Anio, Descripcion, ...)

-- Búsqueda
SP_SearchPublications(query)
```

---

## 🔄 FLUJO DE DATOS

### Ejemplo: Usuario da "like" a una publicación

1. **Cliente (JS)**: Click en botón de like
   ```javascript
   fetch('like_handler.php', { method: 'POST', body: formData })
   ```

2. **Bootstrap** (`html/like_handler.php`):
   ```php
   session_start();
   require '../app/Core/Database.php';
   require '../app/Repositories/PublicationRepository.php';
   require '../app/Controllers/LikeApiController.php';
   
   $repo = new PublicationRepository(Database::getConnection());
   $controller = new LikeApiController($repo);
   $controller->handle();
   ```

3. **API Controller** (`LikeApiController.php`):
   ```php
   public function handle(): void {
       header('Content-Type: application/json');
       // Validar sesión y parámetros
       $publiId = $_POST['publi_id'];
       $userId = $_SESSION['user_id'];
       
       // Llamar al repositorio
       $result = $this->publicationRepo->toggleLike($userId, $publiId);
       
       // Retornar JSON
       echo json_encode([
           'success' => true,
           'like_status' => $result['like_status'],
           'new_like_count' => $result['like_count']
       ]);
   }
   ```

4. **Repository** (`PublicationRepository.php`):
   ```php
   public function toggleLike(int $userId, int $publiId): array {
       $stmt = $this->db->prepare("CALL SP_ToggleLike(?, ?)");
       $stmt->bind_param('ii', $userId, $publiId);
       $stmt->execute();
       $result = $stmt->get_result()->fetch_assoc();
       $stmt->close();
       return $result;
   }
   ```

5. **Base de datos**: Ejecuta `SP_ToggleLike` que inserta/elimina en tabla `Likes`

6. **Respuesta JSON** regresa al cliente:
   ```json
   {
       "success": true,
       "like_status": "liked",
       "new_like_count": 42
   }
   ```

7. **Cliente actualiza UI** sin recargar página

---

## ✅ VALIDACIONES IMPLEMENTADAS

### Seguridad
- ✅ Validación de sesión en todos los controladores
- ✅ Verificación de permisos de usuario (admin vs regular)
- ✅ Prepared statements en todos los SPs
- ✅ Validación de propiedad (solo el autor puede editar/eliminar)
- ✅ Sanitización de HTML con CKEditor
- ✅ Validación de tipos MIME para multimedia

### Integridad de Datos
- ✅ Validación de campos obligatorios
- ✅ Validación de formato de correo electrónico
- ✅ Validación de fecha de nacimiento (mayor de 13 años)
- ✅ Validación de tamaños de archivos
- ✅ Validación de IDs numéricos positivos

---

## 🚀 CÓMO EJECUTAR EL PROYECTO

### Prerrequisitos
1. XAMPP instalado (Apache + MySQL + PHP 7.4+)
2. Base de datos MySQL configurada con stored procedures
3. Extensión `mysqli` habilitada en PHP

### Pasos
1. Copiar proyecto a `c:\xampp\htdocs\BDM-PWCI\CapaIntermedia-main\`
2. Iniciar Apache y MySQL desde XAMPP Control Panel
3. Importar base de datos con stored procedures
4. Acceder a `http://localhost/BDM-PWCI/CapaIntermedia-main/public/inicio.php`

### Usuarios de Prueba
- **Admin**: Para acceder a panel de administración (tipo 0)
- **Regular**: Para crear publicaciones y comentarios (tipo 1)

---

## 📝 CAMBIOS RESPECTO A VERSIÓN ANTERIOR

### ❌ Eliminado (Código Procedural)
- `BD/Connection/Connection.php` - Conexión procedural con mysqli
- `BD/Querys/auth.php` - Funciones procedurales de autenticación
- `BD/Querys/user_functions.php` - Función procedural `getUserDetails()`
- Código inline de DB en archivos anteriores
- Mezcla de lógica de negocio con presentación

### ✅ Agregado (Arquitectura MVC/POO)
- `app/Core/Database.php` - Singleton para conexión
- 4 Repositories con 30+ métodos
- 23 Controllers (14 páginas + 9 API)
- 14 Views limpias sin lógica
- 23 Bootstrap files de 8-12 líneas
- Separación completa de responsabilidades

### 🔄 Migrado
- **14 páginas completas**: De mixed PHP/HTML a MVC puro
- **9 AJAX handlers**: De procedural a API Controllers
- **Toda la lógica DB**: De inline a Repositories
- **Validaciones**: De dispersas a centralizadas en Controllers

---

## 🎓 CONCEPTOS ACADÉMICOS DEMOSTRADOS

Este proyecto demuestra de forma práctica:

1. **Programación Orientada a Objetos**
   - Clases, métodos, propiedades
   - Encapsulación, abstracción
   - Type hinting y return types
   - Constructor injection

2. **Patrones de Diseño**
   - MVC (Model-View-Controller)
   - Repository Pattern
   - Singleton Pattern
   - Bootstrap/Front Controller

3. **Arquitectura de Software**
   - Separación de capas (Presentation, Business, Data)
   - Single Responsibility Principle
   - Dependency Injection
   - API RESTful para AJAX

4. **Bases de Datos**
   - Stored Procedures
   - Prepared Statements
   - BLOB handling con chunking
   - Transacciones implícitas

5. **Desarrollo Web Full Stack**
   - Backend PHP POO
   - Frontend JavaScript vanilla
   - AJAX con fetch API
   - Session management

---

## 📞 SOPORTE Y DOCUMENTACIÓN

Para entender cualquier componente específico:
- **Controllers**: Ver comentarios en cada archivo `app/Controllers/*.php`
- **Repositories**: Documentación inline en `app/Repositories/*.php`
- **Views**: Templates HTML en `app/Views/*.php`
- **SPs**: Revisar definiciones en base de datos MySQL

**Proyecto finalizado**: 100% MVC/POO compliant ✅
**Sin código procedural restante**: ✅
**Todos los endpoints migratos**: ✅
**Todas las vistas separadas**: ✅
