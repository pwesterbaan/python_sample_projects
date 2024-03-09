from __future__ import print_function
def tripleTest(A):
    for indi in range(len(A)):
        Aindi=A[indi]
        for indj in range(len(A)):
            if 3*Aindi==A[indj]:
                print('yes')
                return
    print('no')

def fasterTT(A):
    N=max(A)+1  ## O(n) to find max
    indicatorArray=[None]*N
    for i in A: ## O(n) to record entries
        indicatorArray[i]=1
    for i in A: ## O(1) to compare, done n times
        if 3*i<N:
            if indicatorArray[3*i]==1:
                print('yes')
                return
    print('no')
        
## Testing
A=[3,24,10,19,72]
B=[3,24,10,19,71]
Z=[72,19,10,24,3]
#yes
tripleTest(A)
fasterTT(A)
#no
tripleTest(B)
fasterTT(B)
#yes
tripleTest(Z)
fasterTT(Z)