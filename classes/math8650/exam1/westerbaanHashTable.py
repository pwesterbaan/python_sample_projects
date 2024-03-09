from __future__ import print_function
import random
class HashTable():
    def __init__(self, n):
        self._hashTable=[None]*n
        self._indices=[]
    
    def _hashFunc(self,k):
        return k%n
    
    def insert(self,k,v):
        ind=self._hashFunc(k)
        self._hashTable[ind]=v;
        if ind not in self._indices:
            self._indices.append(ind);
        #pass 
    
    def delete(self,k):
        ind=self._hashFunc(k)
        if ind not in self._indices:
            raise KeyError
        else:
            self._indices[H._indices.index(ind)]=self._indices.pop()
    
    def find(self,k):
        ind=self._hashFunc(k)
        if ind not in self._indices:
            raise KeyError
        else:
            return self._hashTable[ind]
    
    def list(self):
        for ind in self._indices:
            print((ind, self._hashTable[ind]), end=" ")
        print()
    
n=10
H=HashTable(n)
# TODO
#for i in range(20):
#    ind=
#    H.insert()