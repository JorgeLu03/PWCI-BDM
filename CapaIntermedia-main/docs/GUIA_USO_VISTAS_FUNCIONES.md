# 📘 Guía de Uso - Vistas y Funciones SQL Integradas

## ✅ Estado de la Integración

**TODAS las 8 vistas, 2 funciones y 2 triggers están integrados y en uso activo en el proyecto.**

---

## 🔍 Vistas Integradas

### 1. V_PublicacionesConDetalles
**Descripción:** Publicaciones con todos los detalles (categoría, mundial, usuario, contadores de likes y comentarios)

**Usada en:**
- `HomeController` - Página principal (reemplaza SP_MostrarPublicaciones)
- `PublicationRepository::getPublicationsWithDetails()` - Consulta simplificada
- `PublicationRepository::getTopPublications()` - Top publicaciones

**Ventaja:** Elimina 3 JOINs complejos en cada consulta

**Ejemplo de uso:**
```php
// En cualquier controller con PublicationRepository
$publications = $this->pubRepo->getPublicationsWithDetails(
    2,          // Estatus: 2=Aprobadas
    'likes',    // Ordenar por: 'recent', 'likes', 'comments'
    10          // Límite: null = sin límite
);
```

---

### 2. V_ComentariosPublicacion
**Descripción:** Comentarios con información completa del usuario que comentó

**Usada en:**
- `PublicationDetailController` - Detalle de publicación con comentarios
- `PublicationRepository::getCommentsWithUserInfo()` - Consulta de comentarios

**Ventaja:** Elimina JOIN con tabla usuario, incluye foto y nombre automáticamente

**Ejemplo de uso:**
```php
// Obtener comentarios aprobados de una publicación
$comments = $this->pubRepo->getCommentsWithUserInfo(
    $publiId,   // ID de la publicación
    2           // Estatus: 2=Aprobados, 1=Pendientes, 0=Todos
);
```

---

### 3. V_EstadisticasPublicaciones
**Descripción:** Estadísticas agregadas por usuario (total publicaciones, aprobadas, pendientes, likes, comentarios)

**Usada en:**
- `ProfileEditController` - Perfil del usuario con estadísticas
- `MyPublicationsController` - Mis publicaciones con contadores
- `PublicationRepository::getUserPublicationStats()`
- `UserRepository::getUserStatistics()`

**Ventaja:** Calcula automáticamente todos los contadores sin múltiples queries

**Ejemplo de uso:**
```php
// Obtener estadísticas de un usuario
$stats = $this->userRepo->getUserStatistics($userId);
// Retorna: ['Total_Publicaciones', 'Publicaciones_Aprobadas', 'Publicaciones_Pendientes', 
//           'Publicaciones_Rechazadas', 'Total_Likes', 'Total_Comentarios']

// O desde PublicationRepository
$stats = $this->pubRepo->getUserPublicationStats($userId);
```

---

### 4. V_MundialesConEstadisticas
**Descripción:** Mundiales con conteo de publicaciones por mundial

**Usada en:**
- `WorldCupListController` - Listado de mundiales con contadores
- `WorldCupDetailController` - Detalle de mundial con estadísticas
- `CatalogRepository::getWorldCupsWithStats()`
- `CatalogRepository::getWorldCupWithStats()`

**Ventaja:** Muestra cuántas publicaciones tiene cada mundial sin queries adicionales

**Ejemplo de uso:**
```php
// Obtener todos los mundiales con estadísticas
$mundiales = $this->catalogRepo->getWorldCupsWithStats();
// Cada mundial incluye: 'Total_Publicaciones'

// Obtener un mundial específico con estadísticas
$mundial = $this->catalogRepo->getWorldCupWithStats($mundialId);
```

---

## ⚙️ Funciones SQL Integradas

### 1. FN_CalcularEdadUsuario
**Descripción:** Calcula la edad de un usuario a partir de su fecha de nacimiento

**Usada en:**
- `ProfileEditController` - Muestra edad en el perfil
- `MyPublicationsController` - Muestra edad del usuario
- `UserRepository::getUserAge()`
- `UserRepository::getUserProfileWithAge()`

**Ventaja:** Cálculo automático en MySQL, no requiere lógica PHP

**Ejemplo de uso:**
```php
// Obtener edad de un usuario
$edad = $this->userRepo->getUserAge($userId);
// Retorna: int (edad en años) o null si no existe

// Obtener perfil completo con edad calculada
$profile = $this->userRepo->getUserProfileWithAge($userId);
// Incluye campo adicional: ['Edad' => 25]
```

---

### 2. FN_ContarPublicacionesPorEstado
**Descripción:** Cuenta publicaciones de un usuario por estado (Pendiente/Aprobada/Rechazada)

**Usada en:**
- `UserRepository::countUserPublicationsByStatus()`

**Ventaja:** Conteo directo sin queries complejas

**Ejemplo de uso:**
```php
// Contar publicaciones aprobadas de un usuario
$aprobadas = $this->userRepo->countUserPublicationsByStatus($userId, 2);

// Contar publicaciones pendientes
$pendientes = $this->userRepo->countUserPublicationsByStatus($userId, 1);

// Contar publicaciones rechazadas
$rechazadas = $this->userRepo->countUserPublicationsByStatus($userId, 3);

// Estados: 1 = Pendiente, 2 = Aprobada, 3 = Rechazada
```

---

## 🔥 Triggers Automáticos (No requieren código PHP)

### 1. TRG_ActualizarFechaAprobacion
**Descripción:** Actualiza automáticamente `Fec_aprob` cuando una publicación es aprobada o rechazada

**Trigger en:** Tabla `publicacion` (BEFORE UPDATE)

**Funcionamiento:** Cuando `Estatus` cambia de 1 a 2 o 3, establece `Fec_aprob` = NOW() automáticamente

**No requiere cambios en código:** El trigger se ejecuta automáticamente en la BD

---

### 2. TRG_ValidarComentario
**Descripción:** Valida comentarios antes de insertarlos (contenido no vacío, valores por defecto)

**Trigger en:** Tabla `comentario` (BEFORE INSERT)

**Funcionamiento:** 
- Valida que `Contenido` no esté vacío
- Establece `Estatus` = 1 si viene NULL
- Establece `Fec` = NOW() si viene NULL

**No requiere cambios en código:** El trigger se ejecuta automáticamente en la BD

---

## 📊 Resumen de Métodos Nuevos por Repository

### PublicationRepository (4 métodos nuevos)

```php
// 1. Publicaciones con detalles completos (usa V_PublicacionesConDetalles)
getPublicationsWithDetails(int $estatus, string $sortBy, ?int $limit): array

// 2. Comentarios con info de usuario (usa V_ComentariosPublicacion)
getCommentsWithUserInfo(int $publiId, int $estatusComentario): array

// 3. Estadísticas de usuario (usa V_EstadisticasPublicaciones)
getUserPublicationStats(int $userId): ?array

// 4. Top publicaciones populares (usa V_PublicacionesConDetalles)
getTopPublications(int $limit): array
```

### UserRepository (4 métodos nuevos)

```php
// 1. Edad del usuario (usa FN_CalcularEdadUsuario)
getUserAge(int $userId): ?int

// 2. Contar publicaciones por estado (usa FN_ContarPublicacionesPorEstado)
countUserPublicationsByStatus(int $userId, int $estatus): int

// 3. Estadísticas completas (usa V_EstadisticasPublicaciones)
getUserStatistics(int $userId): ?array

// 4. Perfil con edad (usa getUserProfileData + getUserAge)
getUserProfileWithAge(int $userId): ?array
```

### CatalogRepository (2 métodos nuevos)

```php
// 1. Mundiales con estadísticas (usa V_MundialesConEstadisticas)
getWorldCupsWithStats(): array

// 2. Mundial específico con stats (usa V_MundialesConEstadisticas)
getWorldCupWithStats(int $mundialId): ?array
```

---

## 🎯 Controllers Modificados

| Controller | Vista/Función Usada | Cambio Realizado |
|------------|-------------------|------------------|
| HomeController | V_PublicacionesConDetalles | Reemplazó `getApprovedPublications()` por `getPublicationsWithDetails()` |
| MyPublicationsController | V_EstadisticasPublicaciones + FN_CalcularEdadUsuario | Agrega `$userStats` y `$userAge` a la vista |
| ProfileEditController | V_EstadisticasPublicaciones + FN_CalcularEdadUsuario | Usa `getUserProfileWithAge()` y `getUserStatistics()` |
| PublicationDetailController | V_ComentariosPublicacion | Reemplazó `getComments()` por `getCommentsWithUserInfo()` |
| WorldCupListController | V_MundialesConEstadisticas | Reemplazó `getMundiales()` por `getWorldCupsWithStats()` |
| WorldCupDetailController | V_MundialesConEstadisticas | Reemplazó `getWorldCupByID()` por `getWorldCupWithStats()` |

---

## 💡 Ejemplos de Uso Avanzado

### Ejemplo 1: Mostrar estadísticas en el perfil del usuario

```php
// En ProfileEditController o MyPublicationsController
$userId = $_SESSION['user_id'];

// Obtener estadísticas completas
$stats = $this->userRepo->getUserStatistics($userId);

// En la vista PHP:
echo "Total de publicaciones: " . ($stats['Total_Publicaciones'] ?? 0);
echo "Aprobadas: " . ($stats['Publicaciones_Aprobadas'] ?? 0);
echo "Pendientes: " . ($stats['Publicaciones_Pendientes'] ?? 0);
echo "Total de likes recibidos: " . ($stats['Total_Likes'] ?? 0);
echo "Total de comentarios recibidos: " . ($stats['Total_Comentarios'] ?? 0);
```

### Ejemplo 2: Mostrar edad del usuario

```php
// En ProfileEditController
$edad = $this->userRepo->getUserAge($userId);

// En la vista PHP:
if ($edad !== null) {
    echo "Edad: " . $edad . " años";
}
```

### Ejemplo 3: Top 5 publicaciones más populares

```php
// En HomeController o cualquier controller
$topPublications = $this->pubRepo->getTopPublications(5);

// Retorna publicaciones ordenadas por (likes + comentarios) descendente
foreach ($topPublications as $pub) {
    echo $pub['Titulo'] . " - Likes: " . $pub['LikeCount'] . " - Comentarios: " . $pub['CommentCount'];
}
```

### Ejemplo 4: Mundiales con conteo de publicaciones

```php
// En WorldCupListController
$mundiales = $this->catalogRepo->getWorldCupsWithStats();

// En la vista:
foreach ($mundiales as $mundial) {
    echo $mundial['Nombre'] . " (" . $mundial['Anio'] . ")";
    echo " - " . $mundial['Total_Publicaciones'] . " publicaciones";
}
```

---

## 🔧 Verificación de Funcionamiento

Para verificar que todo funciona correctamente:

1. **Abre phpMyAdmin** y verifica que existan:
   ```sql
   -- Vistas
   SHOW FULL TABLES WHERE Table_type = 'VIEW';
   -- Debes ver: V_Categorias, V_DetallesUsuario, V_Mundiales, V_Publicaciones,
   --            V_EstadisticasPublicaciones, V_ComentariosPublicacion, 
   --            V_PublicacionesConDetalles, V_MundialesConEstadisticas
   
   -- Funciones
   SHOW FUNCTION STATUS WHERE Db = 'bdmpwci2';
   -- Debes ver: FN_CalcularEdadUsuario, FN_ContarPublicacionesPorEstado
   
   -- Triggers
   SHOW TRIGGERS;
   -- Debes ver: TRG_ActualizarFechaAprobacion, TRG_ValidarComentario
   ```

2. **Prueba el proyecto:**
   - Inicia sesión
   - Ve a "Inicio" (usa `V_PublicacionesConDetalles`)
   - Ve a "Perfil" (usa `V_EstadisticasPublicaciones` + `FN_CalcularEdadUsuario`)
   - Abre una publicación (usa `V_ComentariosPublicacion`)
   - Ve a "Mundiales" (usa `V_MundialesConEstadisticas`)

---

## ✅ Checklist de Integración

- [x] **8 Vistas creadas** (4 existentes + 4 nuevas)
- [x] **2 Funciones creadas** y en uso
- [x] **2 Triggers creados** y activos
- [x] **3 Repositories modificados** (10 métodos nuevos totales)
- [x] **6 Controllers modificados** para usar las nuevas vistas/funciones
- [x] **Sintaxis PHP verificada** (0 errores)
- [x] **Documentación completa** (este archivo)

---

## 📞 Soporte

Si tienes dudas sobre cómo usar alguna vista o función:

1. Revisa la sección correspondiente en este documento
2. Mira los ejemplos de uso en los Controllers modificados
3. Consulta `NUEVAS_FUNCIONALIDADES.md` para más detalles técnicos
4. Revisa `INSTRUCCIONES_INSTALACION.txt` para queries de prueba

---

**Última actualización:** 14 de noviembre de 2025
**Proyecto:** GolNet - Copa Mundial FIFA 2026
**Estado:** ✅ Totalmente funcional y en producción
