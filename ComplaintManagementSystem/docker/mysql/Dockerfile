# Smart Complaint Management System - MySQL Container
FROM mysql:8.0

# Set environment variables
ENV MYSQL_ROOT_PASSWORD=RootPass@123
ENV MYSQL_DATABASE=complaint_management_system
ENV MYSQL_USER=complaint_user
ENV MYSQL_PASSWORD=ComplaintPass@123

# Copy initialization script
COPY init.sql /docker-entrypoint-initdb.d/01-schema.sql

# Expose port 3306
EXPOSE 3306
