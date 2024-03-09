class semiring:
    """ Semiring structure """
    #------------------------- nested problem classes -----------------------
    class shortest_path:
        __slots__ = '_u', '_e'
        
        def __init__(self):
            self._u=float('inf')
            self._e=0

        def oplus(self,a,b):
            return min(a,b)
        
        def otimes(self,a,b):
            return a+b
        
        def add_unit(self):
            return self._u

        def mult_unit(self):
            return self._e

    class most_reliable:
        __slots__ = '_u', '_e'
        
        def __init__(self):
            self._u=-float('inf')
            self._e=1

        def oplus(self,a,b):
            return max(a,b)
        
        def otimes(self,a,b):
            return a*b
        
        def add_unit(self):
            return self._u

        def mult_unit(self):
            return self._e

    class max_cap:
        __slots__ = '_u', '_e'
        
        def __init__(self):
            self._u=-float('inf')
            self._e=float('inf')

        def oplus(self,a,b):
            return max(a,b)
        
        def otimes(self,a,b):
            return min(a,b)
        
        def add_unit(self):
            return self._u

        def mult_unit(self):
            return self._e

    class trans_closure:
        __slots__ = '_u', '_e'
        
        def __init__(self):
            self._u=0
            self._e=1

        def oplus(self,a,b):
            return a or b
        
        def otimes(self,a,b):
            return a and b
        
        def add_unit(self):
            return self._u

        def mult_unit(self):
            return self._e
    
    #------------------------- public semiring methods -----------------------
    
    def make_semiring(self, p):
        dict={
            'sp': self.shortest_path(),
            'mr': self.most_reliable(),
            'mc': self.max_cap(),
            'tc': self.trans_closure()
        }
        return dict.get(p)