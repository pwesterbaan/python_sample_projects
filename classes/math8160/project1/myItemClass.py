class myItems:
    #------------------------- nested container classes -------------------------
    class edgeContainer:
        """Container class used to store edge weight and reference"""
        __slots__ = '_weight','_edge'

        def __init__(self, weight, edge):
            """initialize items in pq sorted by weight associated with edge"""
#             self._container = container
            self._weight = weight
            self._edge= edge

        def __lt__(self, other):
            """overload __lt__ to compare weights"""
            if self._weight == other._weight:
                return hash(self) < hash(other)
            else:
                return self._weight < other._weight

        def weight(self):
            return self._weight

        def edge(self):
            return self._edge

    class vertexContainer:
        """Container class used to store vertex object and reference to priorityQueue of edges"""
        __slots__ = '_vertex','_priorityQueue'
        def __init__(self, vertex, priorityQueue):
            """initialize vertices in unionFind """
            self._vertex = vertex
            self._priorityQueue = priorityQueue

        def vertex(self):
            return self._vertex

        def heap(self):
            return self._priorityQueue

        def set_heap(self, priorityQueue):
            self._priorityQueue=priorityQueue

    #------------------------- public container methods -------------------------
    def make_edgeItem(self, weight, edge):
        return self.edgeContainer(weight, edge)

    def make_vertexItem(self, vertex, priorityQueue):
        """Initialize a new vertex item with its associated priorityQueue"""
        return self.vertexContainer(vertex, priorityQueue)