# auth.py

import bcrypt
from db_config import get_conn
from typing import Optional

def fetch_user_by_email(email: str):
    sql = """
        SELECT employee_id, first_name, last_name, email, role, password_hash
        FROM employees
        WHERE email = %s AND is_active = 1
        LIMIT 1;
    """
    conn = get_conn()
    try:
        cur = conn.cursor(dictionary=True)
        cur.execute(sql, (email,))
        return cur.fetchone()
    finally:
        conn.close()

def verify_password(plain: str, hashed: Optional[str]) -> bool:
    if not hashed: return False
    try:
        return bcrypt.checkpw(plain.encode(), hashed.encode())
    except Exception:
        return False