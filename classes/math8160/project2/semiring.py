import numpy as np

# handles weird python2 error I encountered on my laptop
# https://stackoverflow.com/a/42502086/11639196
__metaclass__ = type

class semiring:
    __slots__ = '_u', '_e', '_arcsMat', '_weightMat', '_predMat'

    def __init__(self,n,u,e):
        # defines attributes for subclass based 
        # on summary unit u and extension unit e
        self._u=u
        self._e=e
        self._arcsMat=u*np.ones((n,n))
        self._weightMat=u*np.ones((n,n))
        self._predMat=[[None]*n for _ in range(n)]
        for ind in range(n):
            self.set_weight(ind,ind,e)
    
    def oplus(self,a,b):
        #placeholder for subclass specific summary method
        pass
    
    def otimes(self,a,b):
        #placeholder for subclass specific extension method
        pass        

    def updateOp(self,label_w,a_vw,label_v):
        #Update step defined in terms of oplus, otimes
        intermediate_label=self.otimes(a_vw,label_v)
        tmp_label_w=self.oplus(label_w,intermediate_label)
        return tmp_label_w

    #------------------------- get methods -----------------------
    def add_unit(self):
        return self._u

    def mult_unit(self):
        return self._e

    def get_arc(self,i,j):
        return self._arcsMat[i][j]
    
    def get_arcsMat(self):
        return self._arcsMat
    
    def get_weight(self,i,j):
        return self._weightMat[i][j]
    
    def get_weightMat(self):
        return self._weightMat
        
    def get_pred(self,i,j):
        return self._predMat[i][j]
    
    def get_predMat(self):
        return self._predMat

    #------------------------- set methods -----------------------
    def set_arc(self,i,j,val):
        self._arcsMat[i][j]=val

    def set_weight(self,i,j,val):
        self._weightMat[i][j]=val
    
    def set_pred(self,i,j,val):
        self._predMat[i][j]=val

#------------------------- subclasses -----------------------
# provide specific definitions for the summary and
# extension operators along with their associated identities

class shortest_path(semiring):
    __slots__ = '_u', '_e', '_arcsMat', '_weightMat', '_predMat'
    
    def __init__(self,n):
        sumUnit=float('inf')
        extUnit=0
        super(shortest_path,self).__init__(n,sumUnit,extUnit)
    
    def oplus(self,a,b):
        return min(a,b)
    
    def otimes(self,a,b):
        return a+b

class most_reliable(semiring):
    __slots__ = '_u', '_e', '_weightMat', '_predMat'
    
    def __init__(self,n):
        sumUnit=0
        extUnit=1
        super(most_reliable,self).__init__(n,sumUnit,extUnit)
    
    def oplus(self,a,b):
        return max(a,b)
    
    def otimes(self,a,b):
        return a*b

class max_cap(semiring):
    __slots__ = '_u', '_e', '_weightMat', '_predMat'
    
    def __init__(self,n):
        sumUnit=-float('inf')
        extUnit=float('inf')
        super(max_cap,self).__init__(n,sumUnit,extUnit)
    
    def oplus(self,a,b):
        return max(a,b)
    
    def otimes(self,a,b):
        return min(a,b)

class trans_closure(semiring):
    __slots__ = '_u', '_e', '_weightMat', '_predMat'
    
    def __init__(self,n):
        sumUnit=0
        extUnit=1
        super(trans_closure,self).__init__(n,sumUnit,extUnit)
    
    def oplus(self,a,b):
        return a or b
    
    def otimes(self,a,b):
        return a and b