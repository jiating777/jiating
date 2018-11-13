#include<cstdio>
#include<cmath>
#include<iostream>

using namespace std;

struct coordinate {   // �����ά����Ľṹ�� 
	int x;
	int y;
};

bool judge_p(int p, int a, int b);
coordinate add(coordinate p,coordinate g,int a,int n,int b);
void encrypt(coordinate g,coordinate P,coordinate m,int a,int r,int p);
int f1(coordinate P,coordinate q,int a,int p);
int gcd(int a, int b, int &s,int &t);
coordinate m2coordinate(int m, int k, int a, int b, int p);
int sqr(int s);
int mod(int x, int a, int b, int p);


int main() 	{
	int a, b;   //����Բ���߷���Ϊ y^2=x^3+a*x+b	
	int p;
	int p_a;   // �û�A��˽Կ
	int k;//�����k
	coordinate g;  // ��Բ���ߵ�һ������Ԫ 
	coordinate Pm;  //����m��Ӧ����Բ�����ϵĵ� 
	int m,k2;  // ����m�������k ,k��30~50֮�� 
	
	printf("��������Բ���߷���a��b��ֵ��\n");
	scanf("%d %d",&a,&b);
	printf("�����һ������p��\n");
	scanf("%d",&p);
	
	while(!judge_p(p,a,b)){
		printf("p���Ϸ�������������: ");
		scanf("%d",&p);
	}
	printf("�������û�A��˽ԿPa��\n");
	scanf("%d",&p_a);
	
	printf("�����һ������ԪG��\n");
	scanf("%d %d",&g.x,&g.y);
	coordinate P = g;
	P = add(P, g, a, p_a,p);
	printf("����û�A�Ĺ�ԿP��ֵ��\n");
	printf("(%d,%d)\n",P.x,P.y);

	
	printf("����������m����Բ�����ϵĵ�:\n");
	scanf("%d",&m);
	Pm = m2coordinate(m,30,a,b,p);
	printf("(%d,%d)\n",Pm.x,Pm.y);
//	scanf("%d %d",&Pm.x,&Pm.y);

	printf("������k��\n");
	scanf("%d",&k);
	encrypt(g, P, Pm, a, k, p);
	return 0;
}

bool judge_p(int p, int a, int b) {  // �ж���������� p �Ƿ�Ϸ�

    int i = 0;
    for (i=2; i<p; i++) {  // ����Ϊ����������false
        if (p%i == 0)
            return false;
    }
    if((4 * a ^ 3 + 27 * b ^ 2) % p == 0) {
    	return false; 
	}
    return true;
}

coordinate add(coordinate p,coordinate g,int a,int n,int b) {  //abelȺ�ڵļӷ����� aΪ���߷���a��nΪ������bΪ����p

	int k;
	int e;
	for (int i = 0; i < n-1; i++){  // �����û�A�Ĺ�Կ PA=nAG������ n = p_a,�û�A��˽Կ 
		k = f1(p, g, a,b);
		e = p.x;
		p.x = (k*k - p.x - g.x+b)%b;
		while (p.x < 0) {
			p.x = p.x + b;
		}
		p.y = (k*(e - p.x) - p.y + b) % b;
		while (p.y < 0) {
			p.y = p.y + b;
		}
	}
	return p;


}

void encrypt(coordinate g,coordinate P,coordinate m,int a,int r,int p) {
	coordinate c1, c2;
	c1 = g;
	c1 = add(c1, g, a, r, p);
	c2 = P;
	c2 = add(c2, P, a, r, p);
	c2 = add(m, c2, a, 2, p);
	printf("m������Ϊ��\n");
	printf("{(%d,%d),(%d,%d)}\n",c1.x,c1.y,c2.x,c2.y);
}

int f1(coordinate P,coordinate q,int a,int p) {  //�� �˵�ֵ,����ĳ���a/b=a*b^-1��modp��
	int k,c,b,e,t;
	if ((P.x==q.x)&&(P.y==q.y)) {
		c = ((3 * q.x*q.x + a)%p+p)%p;
		b= (2 *P.y);
		gcd(b, p, e,t);
		while (e < 0) {
			e = (e + p)% p;
		}
		k = (c*e+p)%p;
	} else {
		c = ((q.y - P.y) % p + p ) % p;
		b = ((q.x - P.x) % p + p) % p;
		gcd(b, p, e,t);
		while (e < 0) {
			e = (e + p) % p;
		}
		k = (c*e+p)%p;
	}
	return k;
}


int gcd(int a, int b, int &s, int &t) {
	if(a==0 && b==0) {
		return -1;
	}
	if(b == 0) {
		s = 1;
		t = 0;
		return 0;
	}
	int d = gcd(b,a%b,t,s);
	t -= a / b * s;
	return d;
}

int sqr(int s) {  // �ж�һ�����Ƿ���ƽ���� 
	int m = 1;
    while(m*m < s) m++;
    if(m*m == s) {
    	return m;
	} else {
		return -1;
	}
}

int mod(int x, int a, int b, int p) {	
	__int64 tt = 2580*2580%4177*2580+(3*2580)%4177;
	printf("%d",tt%4177);
}


coordinate m2coordinate(int m, int k, int a, int b, int p) {  // ������ת������Բ������,x^3+a*x+b

	int x = m*k;
	int s = x*x*x + a*x + b;
	int j = 1;
	coordinate res;
	while(sqr(s%p) == -1) {
		x += j;
		s = x*x*x + a*x + b;
	}
	res.x = x;
	res.y = mod(x,p,a,b);
	return res;
}

