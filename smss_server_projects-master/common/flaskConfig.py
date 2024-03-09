import os
import random
import string

class Config(object):
    DEBUG = False
    TESTING = False
    # FLASK_SECRET = ''.join(random.SystemRandom().choice(string.ascii_letters + string.digits) for _ in range(42))
    # generating a random key is not a great idea
    # https://stackoverflow.com/questions/27287391/why-not-generate-the-secret-key-every-time-flask-starts

    # A secret key that will be used for securely signing the session cookie and can be used for any other security related needs by extensions or your application.
    SECRET_KEY = 'secret_key'
    FLASK_SECRET = 'flask_secret'
    FLASK_KEY = 'flask_key'
    ALLOWED_FILETYPE = set(['application/pdf'])
    FLASK_HTPASSWD_PATH = '/path/to/.htpasswd'
    JSONIFY_PRETTYPRINT_REGULAR = False
    MAX_CONTENT_LENGTH = 1*1024*1024 #1MB
    SESSION_TYPE = 'filesystem'

class ProductionConfig(Config):
    pass

class DevelopmentConfig(Config):
    DEBUG = True

class TestingConfig(Config):
    TESTING = True
