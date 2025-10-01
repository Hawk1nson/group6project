# db_config.py

import mysql.connector
import os

# Centralized DB connection settings
DB_CONFIG = {
    "host": os.getenv("CDMS_DB_HOST", "127.0.0.1"), # default to localhost if not set
    "port": int(os.getenv("CDMS_DB_PORT", "3307")), # default to 3307 if not set
    "user": os.getenv("CDMS_DB_USER", "cdms_user"),        # CHANGE THIS after initial testing
    "password": os.getenv("CDMS_DB_PASS", "StrongPass!"), # CHANGE THIS after initial testing
    "database": os.getenv("CDMS_DB_NAME", "cdms_db"),   # default to cdms_db if not set
}

def get_conn():
    """Return a live DB connection using settings above."""
    return mysql.connector.connect(**DB_CONFIG)
