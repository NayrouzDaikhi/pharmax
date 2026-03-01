#!/usr/bin/env python3
import mysql.connector

config = {
    'user': 'root',
    'password': '',
    'host': '127.0.0.1',
    'database': 'pharm'
}

cnx = mysql.connector.connect(**config)
cursor = cnx.cursor()

email = 'nayrouzdaikhi@gmail.com'

# Check if user exists
cursor.execute("SELECT id FROM user WHERE email=%s", (email,))
result = cursor.fetchone()

if result:
    print(f"✓ User exists with ID: {result[0]}")
else:
    # Insert user
    cursor.execute("""
        INSERT INTO user (email, password, first_name, last_name, roles, status, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, %s, NOW(), NOW())
    """, (
        email,
        '$2y$13$ZxvKxFAFz3v6/m.K1q9cK.8xZ8D5.v3Z5v3Z5v3Z5v3Z5v3Z5v3Z5',
        'Nayrouz',
        'Daikhi',
        '["ROLE_USER"]',
        'active'
    ))
    cnx.commit()
    print(f"✓ User {email} added successfully!")
    print(f"  User ID: {cursor.lastrowid}")

cursor.close()
cnx.close()
