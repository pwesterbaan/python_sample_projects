import random
import copy

class myStack:
    ## Stack implementation pulled from stacks.pdf class notes
    def __init__(self):
        self._list=[]
    
    def __len__(self):
        return len(self._list)
    
    def is_empty(self):
        return len(self._list)==0
    
    def push(self,item):
        self._list.append(item)
    
    def pop(self):
        if self.is_empty():
            raise IndexError('Stack is Empty')
        return self._list.pop()
    
    def top(self):
        if self.is_empty():
            raise IndexError('Stack is Empty')
        return self._list[-1]
S=myStack()
n=20
for i in range(n):
    S.push(random.randint(10,99))

T=myStack()
##The actual reversing of the elements
for i in range(len(S)):
    T.push(S.pop())

##Copying the sorted stack into the original 
S=copy.deepcopy(T)