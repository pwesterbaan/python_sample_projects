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
    SECRET_KEY = '273d62d078aa48da6a26a95b5d86f012bc6fe586a9d6dfbf9f1edbc41367b836599d3762e6c95ba39789'
    FLASK_SECRET = '3ee469bee58926007804ccde927c7aad57733ac22996da87a2de6c0fb07a19d9bfe173b26a84eecadd18'
    FLASK_KEY = '98b499880c1aca7bc98a1a03c5baf903446faa5baf58d284f4e0a24a433c6cec6ebdd07d7dd838043c5f'
    ALLOWED_FILETYPE = set(['application/pdf'])
    FLASK_HTPASSWD_PATH = '/var/www/mthsc/secure/.htpasswd'
    JSONIFY_PRETTYPRINT_REGULAR = False
    MAX_CONTENT_LENGTH = 1*1024*1024 #1MB
    SESSION_TYPE = 'filesystem'

class ProductionConfig(Config):
    pass

class DevelopmentConfig(Config):
    DEBUG = True

class TestingConfig(Config):
    TESTING = True
