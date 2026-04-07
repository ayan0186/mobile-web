import os
from dotenv import load_dotenv

load_dotenv()

class Config:
    # MySQL Configuration
    MYSQL_HOST = 'localhost'
    MYSQL_USER = 'root'  # Your MySQL username
    MYSQL_PASSWORD = '40633920050122Ay@n'  # Your MySQL password
    MYSQL_DB = 'salon_db'
    MYSQL_CURSORCLASS = 'DictCursor'
    
    # Flask Configuration
    SECRET_KEY = os.environ.get('SECRET_KEY') or 'your-secret-key-here-change-in-production'
    
    # Session Configuration
    SESSION_PERMANENT = False
    SESSION_TYPE = 'filesystem'