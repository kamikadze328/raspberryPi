## Vue 2 + PHP 8  
- Страница авторизации, главное меню сервиса, панель администратора - реализовано в виде SPA.  
- Аутентификация на куках. Пользователю создаётся токен вместе с дополнительной информацией (payload). В ней хранится информация об правах доступа пользователся к ресурсам сервиса. Это сделано для минимизации общения с базой данных, то есть когда приходит запрос от пользователся с токеном, проверяется только наличие этого токена в бд, его права доступа уже получаются из этого самого токена. Соответственно при изменении прав какого-либо пользователя, они будут применены только после повторного входа пользователся в аккаунт, т.е. когда будет сгенерирован новый токен.
- Кроме этого есть служебные токены user_meta - для хранения базовой информации о пользователе для фронтенда, и SUID для сбора и хранения статистики о пользователе